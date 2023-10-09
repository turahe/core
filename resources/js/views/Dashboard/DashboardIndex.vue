<!--
  - This source code is the proprietary and confidential information of
  - Nur Wachid. You may not disclose, copy, distribute,
  -  or use this code without the express written permission of
  - Nur Wachid.
  -
  - Copyright (c) 2023.
  -
  -
  -->

<template>
  <ILayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />
      <div class="flex items-center space-x-3 lg:space-x-6">
        <IMinimalDropdown type="horizontal" placement="bottom-end">
          <IDropdownItem
            @click="redirectToEdit(dashboard)"
            icon="PencilAlt"
            :text="$t('core::dashboard.edit_current')"
          />
          <IDropdownItem
            @click="$iModal.show('newDashboard')"
            icon="Plus"
            :text="$t('core::dashboard.new_dashboard')"
          />
          <IDropdownItem
            @click="destroy(dashboard)"
            icon="Trash"
            :text="$t('core::dashboard.delete_current')"
          />
        </IMinimalDropdown>

        <DropdownSelectInput
          adaptive-width
          :modelValue="dashboard"
          :items="userDashboards"
          label-key="name"
          placement="bottom-end"
          wrapper-class="w-full"
          class="min-w-0 max-w-full sm:max-w-xs"
          value-key="id"
          @change="setActiveDashboard"
        >
          <template v-slot="{ label, toggle }">
            <IButton
              variant="white"
              @click="toggle"
              :class="[
                'w-full',
                { 'pointer-events-none blur': !componentReady },
              ]"
            >
              <span class="truncate">
                <!-- "Dashboard" text allow the blur to be more visible while loading -->
                {{ componentReady ? label : 'Dashboard' }}
              </span>
              <Icon icon="ChevronDown" class="-mr-1 ml-2 h-5 w-5"></Icon>
            </IButton>
          </template>
          <template #label="{ label }">
            <Icon
              v-if="dashboard.is_default"
              class="mr-1 h-5 w-5"
              icon="Star"
            />
            {{ label }}
          </template>
        </DropdownSelectInput>
      </div>
    </template>

    <div
      v-if="!componentReady"
      class="before:box-inherit after:box-inherit columns-1 gap-x-6 lg:columns-2"
    >
      <CardPlaceholder pulse class="mb-5" />
      <CardPlaceholder pulse class="mb-5" size="lg" />
      <CardPlaceholder pulse class="mb-5" />
      <CardPlaceholder pulse class="mb-5" />
      <CardPlaceholder pulse class="mb-5" size="lg" />
      <CardPlaceholder pulse class="mb-5" size="lg" />
      <CardPlaceholder pulse class="mb-5" />
      <CardPlaceholder pulse class="mb-5" />
    </div>

    <draggable
      v-model="mutableCards"
      handle=".dashboard-drag-handle"
      class="before:box-inherit after:box-inherit columns-1 gap-x-6 lg:columns-2"
      item-key="uriKey"
      v-bind="scrollableDraggableOptions"
      @change="saveCardsOrder"
    >
      <template #item="{ element }">
        <div class="mb-5 break-inside-avoid">
          <div class="relative">
            <div class="dashboard-drag-handle absolute left-2 top-5 z-10 block">
              <IButtonIcon
                icon="Selector"
                class="cursor-move"
                icon-class="w-4 h-4"
              />
            </div>
            <component :is="element.component" :card="element" />
          </div>
        </div>
      </template>
    </draggable>
    <IModal
      id="newDashboard"
      size="sm"
      :cancel-title="$t('core::app.cancel')"
      :ok-title="$t('core::app.create')"
      form
      @submit="create"
      :ok-disabled="form.busy"
      @keydown="form.onKeydown($event)"
      :title="$t('core::dashboard.create')"
      @shown="() => $refs.inputNameRef.focus()"
    >
      <IFormGroup label-for="name" :label="$t('core::dashboard.name')" required>
        <IFormInput id="name" ref="inputNameRef" v-model="form.name" />
        <IFormError v-text="form.getError('name')" />
      </IFormGroup>
    </IModal>
  </ILayout>
</template>
<script setup>
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue'
import filter from 'lodash/filter'
import find from 'lodash/find'
import sortBy from 'lodash/sortBy'
import orderBy from 'lodash/orderBy'
import findIndex from 'lodash/findIndex'
import draggable from 'vuedraggable'
import { CancelToken } from '~/Core/resources/js/services/HTTP'
import { useCards } from '~/Core/resources/js/components/Cards/useCards'
import CardPlaceholder from '~/Core/resources/js/components/Cards/CardPlaceholder.vue'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useForm } from '~/Core/resources/js/composables/useForm'

const { t } = useI18n()
const router = useRouter()
const store = useStore()

const { currentUser } = useApp()
const { applyUserConfig } = useCards()
const { scrollableDraggableOptions } = useDraggable()

const { form } = useForm({
  name: '',
})

const mutableCards = ref([])
const dashboard = ref({})
const cards = ref([])
const componentReady = ref(false)
let cancelToken = null

const enabledAndSortedCards = computed(() =>
  sortBy(
    filter(applyUserConfig(cards.value, dashboard.value), card => card.enabled),
    ['order', 'asc']
  )
)

const userDashboards = computed(
  () =>
    orderBy(
      currentUser.value.dashboards,
      ['is_default', 'name'],
      ['desc', 'asc']
    ) || []
)

const defaultDashboard = computed(
  () =>
    find(userDashboards.value, ['is_default', true]) || userDashboards.value[0]
)

function redirectToEdit(dashboard) {
  router.push({
    name: 'edit-dashboard',
    params: {
      id: dashboard.id,
    },
  })
}

function create() {
  form.post('/dashboards').then(dashboard => {
    Innoclapps.success(t('core::dashboard.created'))
    store.commit('users/ADD_DASHBOARD', dashboard)
    redirectToEdit(dashboard)
  })
}

function setActiveDashboard(activeDashboard) {
  dashboard.value = activeDashboard
  nextTick(() => (mutableCards.value = enabledAndSortedCards.value))
}

function saveCardsOrder() {
  let cardsWithConfig = applyUserConfig(cards.value, dashboard.value)

  let payload = cardsWithConfig.map(originalCard => {
    let index = findIndex(mutableCards.value, ['uriKey', originalCard.uriKey])

    return {
      key: originalCard.uriKey,
      enabled: originalCard.enabled,
      order: index === -1 ? originalCard.order : index + 1,
    }
  })

  Innoclapps.request()
    .put(`/dashboards/${dashboard.value.id}`, {
      cards: payload,
    })
    .then(({ data }) => {
      setActiveDashboard(data)
      store.commit('users/UPDATE_DASHBOARD', data)
    })
}

async function fetchCards() {
  let response = await Innoclapps.request().get('/cards', {
    cancelToken: new CancelToken(token => (cancelToken = token)),
  })

  cards.value = response.data
  componentReady.value = true
}

async function destroy(dashboard) {
  await Innoclapps.dialog().confirm()
  await Innoclapps.request().delete(`/dashboards/${dashboard.id}`)

  Innoclapps.success(t('core::dashboard.deleted'))
  store.commit('users/REMOVE_DASHBOARD', dashboard.id)
  setActiveDashboard(defaultDashboard.value)
}

onMounted(() => {
  fetchCards().then(() => {
    nextTick(() => {
      setActiveDashboard(defaultDashboard.value)
    })
  })
})

onBeforeUnmount(() => {
  cancelToken && cancelToken()
})
</script>
