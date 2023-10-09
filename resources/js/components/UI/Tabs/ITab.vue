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
  <Tab as="template" :disabled="disabled" v-slot="{ selected }" ref="tabRef">
    <button
      type="button"
      data-tab="true"
      :class="[
        selected
          ? 'border-primary-500 text-primary-600 dark:border-primary-400 dark:text-primary-300'
          : 'border-transparent text-neutral-600 hover:border-neutral-300 hover:text-neutral-800 dark:text-neutral-100 dark:hover:border-neutral-500 dark:hover:text-neutral-300',
        'group inline-flex min-w-full shrink-0 snap-start snap-always items-center justify-center whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium focus:outline-none sm:min-w-0',
        { 'pointer-events-none opacity-70': disabled },
      ]"
    >
      <Icon
        v-if="icon"
        :icon="icon"
        :class="[
          selected
            ? 'text-primary-500 dark:text-primary-300'
            : 'text-neutral-400 group-hover:text-neutral-500 dark:text-neutral-400 dark:group-hover:text-neutral-300',
          '-ml-0.5 mr-1.5 h-5 w-5',
        ]"
      />

      <slot>
        <span v-text="title" />
      </slot>

      <IBadge
        v-if="badge"
        size="circle"
        wrapper-class="ml-1.5"
        :variant="badgeVariant"
        :text="badge"
      />
    </button>
  </Tab>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue'
import { Tab } from '@headlessui/vue'
import { useActiveElement } from '@vueuse/core'

const emit = defineEmits(['activated'])

const tabRef = ref(null)
const activeElement = useActiveElement()

watch(activeElement, newEl => {
  if (tabRef.value.el.isEqualNode(newEl)) {
    emit('activated')
  }
})

onMounted(() => {
  if (tabRef.value.el.getAttribute('aria-selected') === 'true') {
    emit('activated')
  }
})

const props = defineProps({
  title: String,
  disabled: Boolean,
  icon: String,
  badge: [String, Number],
  badgeVariant: { default: 'info', type: String },
})
</script>
