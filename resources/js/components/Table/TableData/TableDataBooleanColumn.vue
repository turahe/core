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
  <span
    :class="[
      'inline-block h-4 w-4 rounded-full',
      {
        'bg-success-400': passes,
        'bg-danger-400': fails,
      },
    ]"
  ></span>
</template>
<script setup>
import { computed } from 'vue'

import propsDefinition from './props'
const props = defineProps(propsDefinition)

const passes = computed(() => props.formatted === props.column.trueValue)
const fails = computed(() => props.formatted === props.column.falseValue)
</script>
