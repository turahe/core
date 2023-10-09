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
  <div :class="{ relative: show }">
    <slot></slot>
    <div class="absolute inset-0 z-10" v-show="show">
      <div
        class="absolute inset-0 rounded-md bg-neutral-100 opacity-60 dark:bg-neutral-700"
      ></div>
      <div
        v-if="show"
        class="absolute z-20"
        style="
          top: 50%;
          left: 50%;
          transform: translateX(-50%) translateY(-50%);
        "
      >
        <slot name="overlay">
          <ISpinner class="h-5 w-5 text-primary-500 dark:text-neutral-300" />
        </slot>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({
  show: { default: false, type: Boolean },
})
</script>
