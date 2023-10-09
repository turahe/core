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
  <IOverlay :show="overlay">
    <component
      :is="tag"
      :class="[
        'card bg-white dark:bg-neutral-900',
        {
          'ring-1 ring-neutral-600 ring-opacity-5 dark:ring-opacity-50 dark:ring-neutral-700': ring,
          'rounded-lg': rounded,
          shadow: shadow,
          'overflow-hidden': overflowHidden,
        },
      ]"
      v-bind="$attrs"
    >
      <!-- Header -->
      <div
        v-if="header || $slots.header || $slots.actions"
        :class="[
          'flex flex-wrap items-center justify-between border-b border-neutral-200 dark:border-neutral-700 sm:flex-nowrap',
          condensed ? 'px-6 py-3' : 'px-4 py-5 sm:px-6',
          headerClass,
        ]"
      >
        <div class="grow">
          <slot name="header">
            <ICardHeading>{{ header }}</ICardHeading>
          </slot>
          <p
            v-if="description"
            class="mt-1 max-w-2xl text-sm text-neutral-500 dark:text-neutral-200"
            v-text="description"
          />
        </div>
        <div
          v-if="$slots.actions"
          class="shrink-0 sm:ml-4"
          :class="actionsClass"
        >
          <slot name="actions"></slot>
        </div>
      </div>

      <!-- Body -->
      <ICardBody v-if="!noBody" :condensed="condensed">
        <slot></slot>
      </ICardBody>

      <slot v-else></slot>

      <!-- Footer -->
      <ICardFooter
        v-if="$slots.footer"
        :condensed="condensed"
        :class="footerClass"
      >
        <slot name="footer"></slot>
      </ICardFooter>
    </component>
  </IOverlay>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
defineProps({
  tag: { type: [String, Object], default: 'div' },
  header: String,
  headerClass: [String, Array, Object],
  actionsClass: [String, Array, Object],
  footerClass: [String, Array, Object],
  description: String,
  condensed: { default: false, type: Boolean },
  overlay: { default: false, type: Boolean },
  ring: { default: true, type: Boolean },
  rounded: { default: true, type: Boolean },
  shadow: { default: true, type: Boolean },
  overflowHidden: { default: true, type: Boolean },
  noBody: { default: false, type: Boolean },
})
</script>
