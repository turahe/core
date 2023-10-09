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
  <div
    class="flex items-center space-x-1 text-sm text-neutral-600 dark:text-white"
  >
    <span v-t="'core::table.length_menu.show'"></span>

    <DropdownSelectInput
      v-model="collection.perPage"
      @change="$emit('change', $event)"
      :items="collection.perPageOptions"
      :disabled="disabled"
    />

    <span v-t="'core::table.length_menu.entries'"></span>
  </div>
</template>
<script setup>
defineEmits(['change'])
defineProps({
  disabled: { default: false, type: Boolean },
  collection: { required: true, type: Object },
})
</script>
