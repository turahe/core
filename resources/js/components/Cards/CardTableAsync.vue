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
  <Card
    :card="card"
    no-body
    :request-query-string="tableRequestLastQueryString"
    @retrieved="handleCardRetrievedEvent"
    :reload-on-query-string-change="false"
  >
    <template #actions>
      <slot name="actions"></slot>
    </template>
    <div class="mh-56 overflow-y-auto">
      <TableSimple
        ref="tableRef"
        stackable
        :table-id="card.uriKey"
        :table-props="{
          sticky: true,
          maxHeight: '450px',
        }"
        :fields="fields"
        :initial-data="card.items"
        :request-query-string="cardRequestLastQueryString"
        :request-uri="`cards/${card.uriKey}`"
        @data-loaded="tableRequestLastQueryString = $event.requestQueryString"
      >
        <template v-for="(_, name) in $slots" v-slot:[name]="slotData">
          <slot :name="name" v-bind="slotData" />
        </template>
      </TableSimple>
    </div>
  </Card>
</template>
<script setup>
import { ref, computed } from 'vue'
import TableSimple from '~/Core/resources/js/components/Table/Simple/TableSimple.vue'
import get from 'lodash/get'
import { useDates } from '~/Core/resources/js/composables/useDates'

const props = defineProps({
  card: Object,
})

const { maybeFormatDateValue } = useDates()

const tableRequestLastQueryString = ref({})
const cardRequestLastQueryString = ref({})
const tableRef = ref(null)

/**
 * Get the fields for the table
 *
 * @return {Array}
 */
const fields = computed(() => {
  return props.card.fields.map(field => {
    field.formatter = (value, key, item) => {
      return maybeFormatDateValue(value, get(item, key))
    }

    return field
  })
})

/**
 * Card retrieve event
 *
 * @param  {Object} payload
 *
 * @return {Void}
 */
function handleCardRetrievedEvent(payload) {
  cardRequestLastQueryString.value = payload.requestQueryString
  // We must replace the actual table data as the card may have e.q. range
  // parameter which may cause the table data to change but because
  // the request is not performed via the table class, the data will remain the same as before the
  // request and this will make sure that the data is updated
  tableRef.value.replaceCollection(payload.card.items)
}

/**
 * Reload the tbale
 *
 * @return {Void}
 */
function reload() {
  tableRef.value.reload()
}

defineExpose({ reload })
</script>
