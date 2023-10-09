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
  <IDropdownItem
    @click="$emit('click', filterId)"
    :active="isActive"
    :text="name"
    :icon="isDefault ? 'Star' : null"
    prepend-icon
  />
</template>
<script setup>
import { computed } from 'vue'
import { useFilterable } from './useFilterable'

defineEmits(['click'])

const props = defineProps({
  identifier: { required: true, type: String },
  view: { required: true, type: String },
  filterId: { type: Number, required: true },
  name: { type: String, required: true },
})

const { activeFilter, userDefaultFilter } = useFilterable(
  props.identifier,
  props.view
)

/**
 * Indicates whether the current filter is active
 */
const isActive = computed(
  () => activeFilter.value && activeFilter.value.id == props.filterId
)

/**
 * Indicates whether the given filter is default for the current view
 */
const isDefault = computed(() => {
  if (!userDefaultFilter.value) {
    return false
  }

  return props.filterId == userDefaultFilter.value.id
})
</script>
