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
    v-if="!isBetween"
    size="sm"
    type="number"
    :placeholder="placeholder"
    :disabled="readOnly"
    :modelValue="query.value"
    @input="updateValue($event)"
  />
  <div class="flex items-center space-x-2" v-else>
    <IFormInput
      type="number"
      size="sm"
      :placeholder="placeholder"
      :disabled="readOnly"
      :modelValue="query.value[0]"
      @input="updateValue([$event, query.value[1]])"
    />
    <Icon icon="ArrowRight" class="h-4 w-4 shrink-0 text-neutral-600" />
    <IFormInput
      type="number"
      size="sm"
      :placeholder="placeholder"
      :disabled="readOnly"
      :modelValue="query.value[1]"
      @input="updateValue([query.value[0], $event])"
    />
  </div>
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
