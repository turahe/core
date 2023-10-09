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
  <div class="space-x-2">
    <component
      v-for="(option, index) in options"
      :key="'row-' + column.attribute + '-' + index"
      :is="option.swatch_color ? TextBackground : 'span'"
      :color="option.swatch_color || undefined"
      :class="[
        'inline-flex items-center justify-center rounded-full px-2.5 text-sm/5 font-normal dark:!text-white',
        !option.swatch_color ? 'bg-neutral-200 dark:bg-neutral-600' : '',
      ]"
    >
      <Icon
        v-if="option.icon"
        :icon="option.icon"
        class="mr-1.5 h-4 w-4 text-current"
      />
      {{ option.formatted || option.label || option.name }}
    </component>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import castArray from 'lodash/castArray'
import TextBackground from '~/Core/resources/js/components/TextBackground.vue'

import propsDefinition from './props'
const props = defineProps(propsDefinition)

const options = computed(() =>
  castArray(props.row[props.column.attribute] || [])
)
</script>
