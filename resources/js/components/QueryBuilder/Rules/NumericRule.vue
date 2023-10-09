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
  <IFormNumericInput
    v-if="!isBetween"
    size="sm"
    :placeholder="placeholder"
    :modelValue="query.value"
    @input="updateValue($event)"
    :read-only="readOnly"
  />
  <div class="flex items-center space-x-2" v-else>
    <IFormNumericInput
      size="sm"
      :placeholder="placeholder"
      :modelValue="query.value[0]"
      @input="updateValue([$event, query.value[1]])"
      :read-only="readOnly"
    />
    <Icon icon="ArrowRight" class="h-4 w-4 shrink-0 text-neutral-600" />
    <IFormNumericInput
      size="sm"
      :placeholder="placeholder"
      :modelValue="query.value[1]"
      @input="updateValue([query.value[0], $event])"
      :read-only="readOnly"
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

// Prevents warning for vue numeric because if query.value is null
// will throw validation warning in console
if (props.query.value === null) {
  updateValue(0)
}
</script>
