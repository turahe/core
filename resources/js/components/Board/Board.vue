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
    <div class="board-top py-4 px-4 sm:px-6">
      <slot name="top"></slot>
    </div>
    <div
      class="board block space-x-4 overflow-x-scroll whitespace-nowrap px-4 pb-2 sm:px-6"
      :class="boardClass"
      :id="boardId"
    >
      <slot></slot>
    </div>
  </div>
</template>
<script setup>
defineProps({
  boardClass: [String, Array, Object],
  boardId: { required: true, type: String },
})
</script>
<style>
:root {
  --board-top-height: 70px;
}

.board {
  height: calc(100vh - (var(--navbar-height) + var(--board-top-height)));
}

@media (min-width: 1024px) {
  .board-top {
    height: var(--board-top-height);
    max-height: var(--board-top-height);
  }
}
</style>
