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
  <TabPanel ref="panelRef" :unmount="lazy" class="focus:outline-none">
    <slot></slot>
  </TabPanel>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { TabPanel } from '@headlessui/vue'
import { useActiveElement } from '@vueuse/core'

const emit = defineEmits(['activated'])

const props = defineProps({
  lazy: { type: Boolean, default: false },
})

const activeElement = useActiveElement()

const panelRef = ref(null)

watch(
  activeElement,
  newEl => {
    // Is lazy and unmounted
    if (!panelRef.value.el || panelRef.value.$) {
      return
    }

    if (
      newEl.dataset.tab &&
      newEl.id === panelRef.value.$el.getAttribute('aria-labelledby')
    ) {
      emit('activated')
    }
  },
  { flush: 'post' }
)

onMounted(() => {
  if (
    // is lazy?
    !panelRef.value.el.$ &&
    panelRef.value.el.dataset.headlessuiState === 'selected'
  ) {
    emit('activated')
  }
})
</script>
