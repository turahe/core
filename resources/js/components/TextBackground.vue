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
  <span :style="style">
    <slot>{{ text }}</slot>
  </span>
</template>
<script setup>
import { computed } from 'vue'
import { getContrast, shadeColor } from '@/utils'
import hexRgb from 'hex-rgb'

const props = defineProps({
  text: String,
  color: String,
  // When false uses black white color contrast with original background
  shade: { type: Boolean, default: true },
  // Only when shade is true
  bgOpacity: { type: [String, Number], default: 0.2 },
  colorShade: { type: [String, Number], default: -50 },
})

const style = computed(() => {
  if (!props.color || props.color.length !== 7) {
    return null
  }

  if (!props.shade) {
    return {
      background: props.color,
      color: getContrast(props.color),
    }
  }

  const rgbObject = hexRgb(props.color)
  let rgbBackgroud = `rgba(${rgbObject.red}, ${rgbObject.green}, ${rgbObject.blue}, ${props.bgOpacity})`

  return {
    background: rgbBackgroud,
    color: shadeColor(props.color, props.colorShade),
  }
})
</script>
