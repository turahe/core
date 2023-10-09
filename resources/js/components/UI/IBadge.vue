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
  <span :class="[wrapperClass, size === 'circle' ? 'h-5 w-5' : '']">
    <span :class="computedClass" v-bind="$attrs">
      <slot>
        {{ text }}
      </slot>
    </span>
  </span>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { computed } from 'vue'

const colorMaps = {
  neutral: 'bg-neutral-200 text-neutral-700 border border-neutral-300',
  primary: 'bg-primary-100 text-primary-800 border border-primary-200',
  success: 'bg-success-100 text-success-800 border border-success-200',
  info: 'bg-info-100 text-info-800 border border-info-200',
  warning: 'bg-warning-100 text-warning-800 border border-warning-200',
  danger: 'bg-danger-100 text-danger-800 border border-danger-200',
}

const sizeMaps = {
  sm: 'px-2.5 py-0.5 text-xs',
  lg: 'px-3 py-0.5 text-sm',
  circle: 'px-1 min-h-full min-w-full justify-center text-xs',
}

const props = defineProps({
  variant: { default: 'neutral', type: String },
  rounded: { type: Boolean, default: true },
  text: [String, Number],
  wrapperClass: [String, Array, Object],
  size: {
    default: 'sm',
    type: String,
    validator(value) {
      return ['sm', 'lg', 'circle'].includes(value)
    },
  },
})

const computedClass = computed(() => [
  'inline-flex items-center font-medium',
  colorMaps[props.variant],
  sizeMaps[props.size],
  props.rounded || props.size === 'circle' ? 'rounded-full' : null,
])
</script>
