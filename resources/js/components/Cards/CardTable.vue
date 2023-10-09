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
  <Card no-body :card="card" @retrieved="prepareComponent($event.card)">
    <ITable v-if="hasData" sticky max-height="450px" :id="tableId">
      <thead>
        <tr>
          <th
            v-for="field in fields"
            :key="'th-' + field.key"
            ref="thRefs"
            :class="[
              'text-left',
              {
                hidden: field.isStacked,
              },
            ]"
            v-text="field.label"
          />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in mutableCard.items"
          :key="item[mutableCard.primaryKey]"
        >
          <td
            v-for="field in fields"
            :key="'td-' + field.key"
            :class="{
              'whitespace-nowrap': !field.isStacked,
              hidden: field.isStacked,
            }"
          >
            <span v-if="field.key === fields[0].key && item.path">
              <router-link class="link" :to="item.path">
                {{ item[field.key] }}
              </router-link>
            </span>
            <span v-else>
              {{
                field.formatter
                  ? field.formatter(item[field.key], field.key, item)
                  : item[field.key]
              }}
            </span>
            <template v-if="field.key === fields[0].key">
              <p
                v-for="stackedField in stackedFields"
                :key="'stacked-' + stackedField.key"
                class="flex items-center font-normal"
              >
                <span
                  class="mr-1 font-medium text-neutral-800 dark:text-neutral-100"
                >
                  {{ stackedField.label }}:
                </span>
                <span class="text-neutral-700 dark:text-neutral-300">
                  {{
                    stackedField.formatter
                      ? stackedField.formatter(
                          item[stackedField.key],
                          stackedField.key,
                          item
                        )
                      : item[stackedField.key]
                  }}
                </span>
              </p>
            </template>
          </td>
        </tr>
      </tbody>
    </ITable>
    <p
      v-else
      class="pb-16 pt-12 text-center text-sm text-neutral-400 dark:text-neutral-300"
      v-text="emptyText"
    />
  </Card>
</template>
<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useDates } from '~/Core/resources/js/composables/useDates'
import get from 'lodash/get'
import { useI18n } from 'vue-i18n'
import { useEventListener } from '@vueuse/core'
import { useResponsiveTable } from '~/Core/resources/js/components/Table/useResponsiveTable'

import { randomString } from '@/utils'

const { isColumnVisible } = useResponsiveTable()

const props = defineProps({
  card: Object,
  stackable: { type: Boolean, default: true },
})

const { maybeFormatDateValue } = useDates()
const { t } = useI18n()
const tableId = randomString()
const thRefs = ref([])
const mutableCard = ref({})
const stackedFields = computed(() =>
  mutableCard.value.fields.filter(field => field.isStacked)
)

const fields = computed(() => {
  return mutableCard.value.fields.map(field => {
    field.formatter = (value, key, item) => {
      return maybeFormatDateValue(value, get(item, key))
    }

    return field
  })
})

const emptyText = computed(
  () => mutableCard.value.emptyText || t('core::app.not_enough_data')
)

const hasData = computed(() => mutableCard.value.items.length > 0)

function prepareComponent(card) {
  mutableCard.value = card
}

function stackColumns() {
  const tableWrapperEl = document.getElementById(tableId)

  fields.value.forEach((field, idx) => {
    if (idx > 0 && thRefs.value[idx]) {
      mutableCard.value.fields[idx].isStacked = !isColumnVisible(
        // el
        thRefs.value[idx],
        tableWrapperEl
      )
    }
  })
}

prepareComponent(props.card)

if (props.stackable) {
  useEventListener(window, 'resize', stackColumns)
}

onMounted(() => {
  props.stackable && nextTick(stackColumns)
})
</script>
