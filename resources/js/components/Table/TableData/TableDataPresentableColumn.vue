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
  <router-link
    v-for="(presentable, index) in presentables"
    :key="'row-' + column.attribute + '-' + index"
    class="link block"
    :to="presentable.path"
  >
    {{ presentable.display_name }}
  </router-link>
</template>
<script setup>
import { computed } from 'vue'
import castArray from 'lodash/castArray'

import propsDefinition from './props'
const props = defineProps(propsDefinition)

const presentables = computed(() =>
  castArray(props.row[props.column.attribute] || [])
)
</script>
