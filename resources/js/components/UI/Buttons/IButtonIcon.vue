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
  <button
    v-bind="$attrs"
    type="button"
    @click="handleClickEvent"
    :class="[
      'flex items-center rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-neutral-100',
      computedClass,
    ]"
  >
    <slot>
      <Icon :icon="icon" :class="['pointer-events-none', iconClass]" />
    </slot>
  </button>
</template>
<script>
export default {
  inheritAttrs: false,
}
const colorMaps = {
  secondary:
    'text-neutral-600 hover:text-neutral-800 focus:ring-primary-500 dark:text-white dark:hover:text-neutral-400',
  primary:
    'text-primary-600 hover:text-primary-700 focus:ring-primary-500 dark:text-primary-400 dark:hover:text-primary-500',
  success:
    'text-success-600 hover:text-success-700 focus:ring-success-500 dark:text-success-400 dark:hover:text-success-500',
  info: 'text-info-600 hover:text-info-700 focus:ring-info-500 dark:text-info-400 dark:hover:text-info-500',
  warning:
    'text-warning-600 hover:text-warning-700 focus:ring-warning-500 dark:text-warning-400 dark:hover:text-warning-500',
  danger:
    'text-danger-600 hover:text-danger-700 focus:ring-danger-500 dark:text-danger-400 dark:hover:text-danger-500',
}
</script>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const emit = defineEmits(['click'])

const props = defineProps({
  variant: {
    type: String,
    default: 'secondary',
    validator(value) {
      return Object.keys(colorMaps).includes(value)
    },
  },
  to: [Object, String],
  icon: { required: true, type: String },
  iconClass: {
    type: [String, Array, Object],
    default: 'w-5 h-5',
  },
})

const router = useRouter()

const computedClass = computed(() => colorMaps[props.variant])

function handleClickEvent(e) {
  if (props.to) {
    router.push(props.to)
  } else {
    emit('click', e)
  }
}
</script>
