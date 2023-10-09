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
  <span class="button-group relative z-0 inline-flex rounded-md shadow-sm">
    <slot></slot>
  </span>
</template>
<style>
.button-group > button:first-child {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  border-right: 0;
}

.button-group > button:last-child {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

/* All children except first and last */
.button-group > button:nth-child(1n + 2):not(:last-child) {
  border-radius: 0;
  border-right: 0;
}
</style>
