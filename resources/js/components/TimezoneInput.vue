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
  <ICustomSelect
    v-model="timezone"
    :input-id="fieldId || 'timezone'"
    :clearable="clearable"
    :placeholder="placeholder"
    :options="timezones"
  >
    <template #option="option">
      {{ label(option) }}
    </template>
  </ICustomSelect>
</template>
<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useVModel } from '@vueuse/core'
const store = useStore()

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: null,
  fieldId: String,
  placeholder: { type: String, default: '' },
  clearable: { default: false },
})

const timezone = useVModel(props, 'modelValue', emit)

function label(option) {
  return 'UTC/GMT ' + moment.tz(option.label).format('Z') + ' ' + option.label
}

const timezones = computed(() => store.state.timezones)

// Check if the timezones are set in the store
// If not, make a request to fetch the timezones and set them in for future usage
if (timezones.value.length === 0) {
  store.dispatch('fetchTimezones')
}
</script>
