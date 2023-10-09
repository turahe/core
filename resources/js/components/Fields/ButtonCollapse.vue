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
  <IButton
    @click="$emit('update:collapsed', !collapsed)"
    variant="white"
    size="sm"
  >
    {{ collapseText }}
    <span v-show="collapsed" class="ml-1">
      {{ total > 0 ? '(' + total + ')' : '' }}
    </span>
  </IButton>
</template>
<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

defineEmits(['update:collapsed'])

const props = defineProps({
  collapsed: { required: true, type: Boolean },
  total: Number,
})

const { t } = useI18n()

const collapseText = computed(() =>
  props.collapsed ? t('core::fields.more') : t('core::fields.less')
)
</script>
