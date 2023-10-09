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
  <IFormInput
    type="text"
    :placeholder="placeholder"
    :disabled="readOnly"
    size="sm"
    :modelValue="query.value"
    @input="updateValue($event)"
  />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { toRef, computed } from 'vue'
import { useType } from './useType'
import propsDefinition from './props'
import { useI18n } from 'vue-i18n'

const props = defineProps(propsDefinition)

const { t } = useI18n()

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

const placeholder = computed(() =>
  t('core::filters.placeholders.enter', {
    label: props.operand ? props.operand.label : props.rule.label,
  })
)
</script>
