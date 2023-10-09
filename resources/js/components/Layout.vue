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
  <Teleport to="#navbar-actions">
    <slot name="actions"></slot>
  </Teleport>

  <main
    id="main"
    :class="[
      'relative flex-1 focus:outline-none',
      { 'overflow-y-auto': scrollable },
    ]"
    v-bind="$attrs"
  >
    <div :class="{ 'py-8 sm:py-6': !full }">
      <div :class="['mx-auto', { 'px-4 sm:px-6 lg:px-8': !full }]">
        <div :class="{ 'sm:py-4': !full }">
          <IOverlay :show="overlay">
            <div
              v-if="$slots.actions"
              :class="[
                'mb-4 flex justify-end lg:hidden',
                { 'px-4 pt-10 sm:px-6 lg:px-8': full },
              ]"
            >
              <slot name="actions"></slot>
            </div>
            <slot></slot>
          </IOverlay>
        </div>
      </div>
    </div>
  </main>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
defineProps({
  title: String,
  overlay: { type: Boolean, default: false },
  full: { type: Boolean, default: false },
  scrollable: { type: Boolean, default: true },
})
</script>
