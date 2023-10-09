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
    v-if="row[column.attribute]"
    class="inline-flex max-w-[170px] items-center truncate"
  >
    <IAvatar
      :src="row[column.attribute].avatar_url"
      :alt="row[column.attribute].name"
      size="xs"
    />

    <span class="ml-1" v-text="row[column.attribute].name"></span>
  </span>
</template>
<script setup>
import propsDefinition from './props'
defineProps(propsDefinition)
</script>
