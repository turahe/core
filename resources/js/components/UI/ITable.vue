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
    class="table-responsive"
    ref="elRef"
    :style="{ maxHeight: maxHeight }"
    :class="[
      wrapperClass,
      {
        'table-sticky-header': sticky,
      },
    ]"
  >
    <div
      v-bind="$attrs.style"
      :class="[
        $attrs.class,
        { shadow: shadow },
        {
          'border-x border-b border-neutral-200 dark:border-neutral-800':
            bordered,
        },
      ]"
    >
      <table
        :class="[
          'table-primary',
          { 'table-condensed': condensed, 'border-separate': sticky },
        ]"
        v-bind="tableAttrs"
        :style="{ borderSpacing: sticky ? 0 : undefined }"
      >
        <slot></slot>
      </table>
    </div>
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, computed, useAttrs } from 'vue'

defineProps({
  maxHeight: String,
  condensed: Boolean,
  wrapperClass: [String, Object, Array],
  shadow: { default: true, type: Boolean },
  bordered: { default: false, type: Boolean },
  sticky: { default: false, type: Boolean },
})

const elRef = ref(null)
const attrs = useAttrs()

const tableAttrs = computed(() => {
  const result = { ...attrs }
  delete result.class
  delete result.style
  return result
})

defineExpose({ $el: elRef })
</script>
