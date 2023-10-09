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
  <div class="mb-2 md:mb-0 md:w-auto">
    <IButton
      v-if="config.customizeable && withCustomizeButton"
      variant="white"
      icon="Cog"
      @click="() => $refs.customizationRef.showModal()"
    />
  </div>

  <!-- Settings Modal -->
  <CustomizeTableSettings
    ref="customizationRef"
    :config="config"
    :table-id="tableId"
    :url-path="urlPath"
  />
</template>
<script setup>
import { ref } from 'vue'
import CustomizeTableSettings from './TableSettingsCustomization.vue'

defineProps({
  config: { required: true, type: Object },
  tableId: { required: true, type: String },
  urlPath: { required: true, type: String },
  withCustomizeButton: { type: Boolean, default: false },
  resourceName: { type: String, required: true },
})

const customizationRef = ref(null)

defineExpose({ customizationRef })
</script>
