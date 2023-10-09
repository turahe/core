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
  <SwitchGroup as="div" class="flex items-center">
    <SwitchLabel
      v-if="label"
      :class="[
        labelClass,
        'mr-4 text-sm text-neutral-800 dark:text-neutral-100',
      ]"
      v-text="label"
    />
    <Switch
      v-model="enabled"
      :class="[
        enabled ? 'bg-primary-600' : 'bg-neutral-200 dark:bg-neutral-500',
        disabled ? 'pointer-events-none opacity-60' : '',
        'relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
      ]"
    >
      <!-- Circle -->
      <span
        aria-hidden="true"
        :class="[
          enabled
            ? 'translate-x-5 dark:bg-neutral-300'
            : 'translate-x-0 dark:bg-neutral-400',
          'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
        ]"
      />
    </Switch>
  </SwitchGroup>
</template>
<script setup>
import { ref, watch } from 'vue'
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
  label: String,
  modelValue: {},
  labelClass: [Array, String, Object],
  disabled: { type: Boolean, default: false },
  value: { default: true },
  uncheckedValue: { default: false },
  disabled: Boolean,
})

const enabled = ref(false)

watch(enabled, newVal => {
  const value = newVal === true ? props.value : props.uncheckedValue

  if (value != props.modelValue) {
    emit('update:modelValue', value)
    emit('change', value)
  }
})

watch(
  () => props.modelValue,
  newVal => {
    enabled.value = newVal == props.value
  },
  { immediate: true }
)
</script>
