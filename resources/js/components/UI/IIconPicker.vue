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
  <div class="flex flex-wrap">
    <div v-for="icon in icons" :key="icon[valueField]">
      <IButton
        :disabled="disabled"
        :class="[
          'mr-1.5 mb-1',
          {
            'bg-neutral-300 dark:bg-neutral-600 sm:bg-neutral-200':
              selected === icon[valueField],
          },
        ]"
        :size="size"
        @click="pickIcon(icon[valueField])"
        :icon="icon.icon"
        variant="white"
        v-i-tooltip="icon.tooltip"
      />
    </div>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue'
const emit = defineEmits(['update:modelValue', 'change'])
const props = defineProps({
  modelValue: [String, Number],
  label: String,
  disabled: Boolean,
  size: { type: String, default: 'md' },
  valueField: { default: 'icon' },
  icons: {
    type: Array,
    default: function () {
      return [
        {
          icon: 'Mail',
          tooltip: null,
        },
        {
          icon: 'PencilAlt',
          tooltip: null,
        },
        {
          icon: 'OfficeBuilding',
          tooltip: null,
        },
        {
          icon: 'Phone',
          tooltip: null,
        },
        {
          icon: 'Calendar',
          tooltip: null,
        },
        {
          icon: 'Collection',
          tooltip: null,
        },
        {
          icon: 'Bell',
          tooltip: null,
        },
        {
          icon: 'AtSymbol',
          tooltip: null,
        },
        {
          icon: 'Briefcase',
          tooltip: null,
        },
        {
          icon: 'Chat',
          tooltip: null,
        },
        {
          icon: 'CheckCircle',
          tooltip: null,
        },
        {
          icon: 'BookOpen',
          tooltip: null,
        },
        {
          icon: 'Camera',
          tooltip: null,
        },
        {
          icon: 'Truck',
          tooltip: null,
        },
        {
          icon: 'Folder',
          tooltip: null,
        },
        {
          icon: 'DeviceMobile',
          tooltip: null,
        },
        {
          icon: 'Users',
          tooltip: null,
        },
        {
          icon: 'CreditCard',
          tooltip: null,
        },
        {
          icon: 'Clock',
          tooltip: null,
        },
        {
          icon: 'ShieldExclamation',
          tooltip: null,
        },
        {
          icon: 'WrenchScrewdriver',
          tooltip: null,
        },
      ]
    },
  },
})

const selected = ref(props.modelValue)

function pickIcon(icon) {
  selected.value = icon
  emit('update:modelValue', icon)
  emit('change', icon)
}

watch(
  () => props.modelValue,
  newVal => {
    selected.value = newVal
    emit('update:modelValue', selected.value)
    emit('change', selected.value)
  }
)
</script>
