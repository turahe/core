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
  <IButton :icon="icon" />
</template>
<script setup>
defineProps({
  icon: { default: 'X', type: String },
})
</script>
