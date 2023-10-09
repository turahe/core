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
  <div>
    <Transition
      leave-active-class="transition ease-in duration-1000"
      leave-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <small class="text-sm text-neutral-400 dark:text-neutral-200">
        <slot>
          {{ message }}
        </slot>
      </small>
    </Transition>
  </div>
</template>
<script setup>
defineProps(['message'])
</script>
