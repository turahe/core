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
  <ICard :overlay="isCardLoading">
    <div class="text-lg sm:flex sm:flex-col sm:items-center md:flex-row">
      <div class="group truncate md:grow">
        <div class="flex items-center px-7 pt-4 md:pb-4">
          <ICardHeading class="truncate">{{ card.name }}</ICardHeading>
          <div
            v-if="card.helpText"
            class="ml-2 mt-px"
            v-i-tooltip.bottom.light="card.helpText"
          >
            <Icon
              icon="QuestionMarkCircle"
              class="h-4 w-4 cursor-help text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
            />
          </div>
        </div>
      </div>
      <div
        class="-mt-2 ml-3.5 flex shrink-0 space-x-2 px-3 py-2 sm:-mt-0 sm:ml-0 sm:px-7 sm:py-4"
      >
        <slot name="actions"></slot>

        <DropdownSelectInput
          v-if="card.withUserSelection"
          :items="usersForSelection"
          label-key="name"
          value-key="id"
          placement="bottom-end"
          @change="fetch"
          v-model="user"
        />
        <DropdownSelectInput
          v-if="hasRanges"
          v-model="selectedRange"
          placement="bottom-end"
          :items="card.ranges"
          @change="fetch"
        />
      </div>
    </div>
    <slot></slot>
  </ICard>
</template>

<script setup>
import { ref, unref, watch, computed } from 'vue'
import find from 'lodash/find'
import { useI18n } from 'vue-i18n'
import { useLoader } from '~/Core/resources/js/composables/useLoader'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'

const emit = defineEmits(['retrieved'])

const props = defineProps({
  card: Object,
  loading: Boolean,
  reloadOnQueryStringChange: { type: Boolean, default: true },
  requestQueryString: Object,
})

const { t } = useI18n()

const { setLoading, isLoading } = useLoader()

const { users } = useApp()

const user = ref(null)

/**
 * Get the users for the selection dropdown
 */
const usersForSelection = computed(() =>
  [
    {
      id: null,
      name: t('core::app.all'),
    },
    ...(props.card.users || users.value),
  ].map(user => ({ id: user.id, name: user.name }))
)

const isCardLoading = computed(() => props.loading || isLoading.value)

/**
 * Indicates whether the card has ranges
 */
const hasRanges = computed(() => props.card.ranges.length > 0)

if (
  props.card.withUserSelection !== false &&
  typeof props.card.withUserSelection === 'number'
) {
  user.value = find(usersForSelection.value, [
    'id',
    props.card.withUserSelection,
  ])
}

const selectedRange = ref(
  find(props.card.ranges, range => range.value === props.card.range) ||
    props.card.ranges[0]
)

/**
 * Watch the requst query string for changes
 * When changed, refresh the card
 */
watch(
  () => props.requestQueryString,
  () => {
    props.reloadOnQueryStringChange && fetch()
  },
  { deep: true }
)

/**
 * Fetch the card
 */
function fetch() {
  setLoading(true)

  let queryString = {
    range: unref(selectedRange).value,
    ...(props.requestQueryString || {}),
  }

  if (props.card.withUserSelection) {
    queryString.user_id = user.value ? user.value.id : null
  }

  Innoclapps.request()
    .get(`/cards/${props.card.uriKey}`, {
      params: queryString,
    })
    .then(({ data: card }) =>
      emit('retrieved', {
        card: card,
        requestQueryString: queryString,
      })
    )
    .finally(() => setLoading(false))
}

if (props.card.refreshOnActionExecuted) {
  useGlobalEventListener('action-executed', fetch)
}

useGlobalEventListener('refresh-cards', fetch)
</script>
