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
  <div class="mt-3 mb-1">
    <h5
      class="text-base font-medium text-neutral-800 dark:text-white"
      :class="[field.titleIcon ? 'inline-flex items-center' : '']"
    >
      <Icon
        v-if="field.titleIcon"
        :icon="field.titleIcon"
        class="mr-1.5 h-4 w-4 text-current"
      />

      <span v-text="field.title"></span>
    </h5>
    <p
      v-html="field.message"
      class="text-sm text-neutral-600 dark:text-neutral-300"
    />
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
const props = defineProps({
  field: { type: Object, require: true },
})

props.field.fill = () => {}
props.field.handleChange = () => {}
props.field.isDirty = () => {
  return false
}
</script>
