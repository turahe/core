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
  <td
    v-bind="cellAttributes"
    :class="[
      'whitespace-normal',
      isSelected && '!bg-neutral-50 dark:!bg-neutral-800',
      { 'text-center': isCentered },
    ]"
  >
    <div
      :class="{
        flex: isSelectable,
        'mr-5': isCentered && isSortable,
        'whitespace-pre-line': column.newlineable
      }"
    >
      <IFormCheckbox
        v-if="isSelectable"
        :class="['mr-4', { 'ml-2': condensed }]"
        @click="$emit('selected', row)"
        :checked="isSelected"
      />

      <slot :formatted="formatted">
        {{ formatted }}
      </slot>
    </div>
  </td>
</template>
<script setup>
import { computed } from 'vue'
import get from 'lodash/get'

defineEmits(['selected'])

const props = defineProps({
  column: { required: true, type: Object },
  row: { required: true, type: Object },
  condensed: Boolean,
  isCentered: Boolean,
  isSortable: Boolean,
  isSelected: Boolean,
  isSelectable: Boolean,
})

const cellAttributes = computed(() => ({
  style: {
    width: props.column.width,
    'min-width': props.column.minWidth,
  },
  class: [
    props.column.tdClass,
    {
      'is-primary table-sticky-column': props.column.primary === true,
    },
  ],
}))

/**
 * Get the formatted value
 *
 * lodash get does deep dot notiation search too, used for relations
 */
const formatted = computed(() => {
  if (props.column.relationField) {
    // Allow as well manually specifying path via the attribute e.q. relation.attribute
    return (
      get(props.row, 'displayAs.' + props.column.attribute) ||
      get(
        props.row,
        !props.column.attribute.includes('.')
          ? props.column.attribute + '.' + props.column.relationField
          : props.column.attribute
      )
    )
  }

  return (
    get(props.row, 'displayAs.' + props.column.attribute) ||
    get(props.row, props.column.attribute)
  )
})
</script>
