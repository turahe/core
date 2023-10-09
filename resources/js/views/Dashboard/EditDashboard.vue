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
  <ILayout class="dashboard-edit">
    <template #actions>
      <NavbarSeparator class="hidden lg:block" v-show="componentReady" />
      <IButton
        v-show="componentReady"
        type="button"
        @click="update"
        size="sm"
        :disabled="form.busy"
        :text="$t('core::app.save')"
      />
    </template>

    <div class="mx-auto max-w-7xl lg:max-w-6xl">
      <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <ICard class="mb-4" :overlay="!componentReady">
          <IFormGroup
            label-for="name"
            :label="$t('core::dashboard.name')"
            required
          >
            <IFormInput id="name" v-model="form.name" />
            <IFormError v-text="form.getError('name')" />
          </IFormGroup>
          <IFormGroup v-if="canChangeDefaultState">
            <IFormCheckbox
              id="is_default"
              name="is_default"
              v-model:checked="form.is_default"
              :label="$t('core::dashboard.default')"
            />
            <IFormError v-text="form.getError('is_default')" />
          </IFormGroup>
        </ICard>
        <div
          v-for="card in cardsWithConfigApplied"
          :key="card.uriKey"
          class="mb-6"
        >
          <IFormCheckbox
            :id="'status-' + card.uriKey"
            class="mb-2"
            v-model:checked="status[card.uriKey]"
            :label="$t('core::dashboard.cards.enabled')"
          />

          <p v-if="card.description" class="my-2" v-text="card.description" />

          <div class="pointer-events-none block h-full w-full opacity-70">
            <component :is="card.component" :card="card" />
          </div>
        </div>
      </form>
    </div>
  </ILayout>
</template>
<script setup>
import { ref, shallowRef, computed, nextTick } from 'vue'
import find from 'lodash/find'
import { useCards } from '~/Core/resources/js/components/Cards/useCards'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { useForm } from '~/Core/resources/js/composables/useForm'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const store = useStore()
const { currentUser, setPageTitle } = useApp()
const { applyUserConfig } = useCards()

const status = ref({})
const allCardsForDashboard = shallowRef({})
const componentReady = ref(false)

const { form } = useForm({
  cards: [],
  name: null,
  is_default: false,
})

const allUserDashboards = computed(() => currentUser.value.dashboards)

const totalDashboards = computed(() => allUserDashboards.value.length)

const dashboardBeingEdited = computed(() =>
  find(allUserDashboards.value, ['id', Number(route.params.id)])
)

const cardsWithConfigApplied = computed(() =>
  applyUserConfig(allCardsForDashboard.value, dashboardBeingEdited.value)
)

const canChangeDefaultState = computed(() => {
  // Allow to set as default on the last dashboard which is not default
  if (totalDashboards.value === 1) {
    return true
  }

  return (
    totalDashboards.value > 1 && dashboardBeingEdited.value.is_default === false
  )
})

function update() {
  // Map the status values in the form
  form.set(
    'cards',
    cardsWithConfigApplied.value.map(card => ({
      key: card.uriKey,
      order: card.order,
      enabled: status.value[card.uriKey],
    }))
  )

  form.put(`/dashboards/${dashboardBeingEdited.value.id}`).then(dashboard => {
    Innoclapps.success(t('core::dashboard.updated'))
    store.commit('users/UPDATE_DASHBOARD', dashboard)
    router.push({ name: 'dashboard' })
  })
}

async function prepareComponent() {
  if (!dashboardBeingEdited.value) {
    router.push({ name: 'not-found' })
    return
  }

  let { data: cards } = await Innoclapps.request().get('/cards')

  allCardsForDashboard.value = cards

  await nextTick()

  // Set the status
  cardsWithConfigApplied.value.forEach(card => {
    status.value[card.uriKey] = card.enabled
  })

  setPageTitle(dashboardBeingEdited.value.name)

  form.set('name', dashboardBeingEdited.value.name)
  form.set('is_default', dashboardBeingEdited.value.is_default)

  componentReady.value = true
}

prepareComponent()
</script>
<style>
.dashboard-edit .hide-when-editing {
  display: none !important;
}
</style>
