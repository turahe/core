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
  <div
    v-if="!isDateIsOperator && !isDateWasOperator"
    class="flex items-center space-x-2"
  >
    <DatePicker
      v-if="!isBetween"
      size="sm"
      class="flex-1"
      :placeholder="$t('core::filters.placeholders.select_date')"
      :modelValue="query.value"
      @input="updateValue($event)"
      :disabled="readOnly"
    />
    <DatePicker
      v-if="isBetween"
      :placeholder="$t('core::filters.placeholders.select_date')"
      :modelValue="query.value[0]"
      size="sm"
      @input="updateValue([$event, query.value[1]])"
      :disabled="readOnly"
    />
    <Icon
      v-if="isBetween"
      icon="ArrowRight"
      class="h-4 w-4 shrink-0 text-neutral-600"
    />
    <DatePicker
      v-if="isBetween"
      size="sm"
      :placeholder="$t('core::filters.placeholders.select_date')"
      :min-date="query.value[0] || null"
      :disabled="readOnly || !query.value[0]"
      :modelValue="query.value[1]"
      @input="updateValue([query.value[0], $event])"
    />
  </div>
  <IFormSelect
    v-else
    size="sm"
    :modelValue="query.value"
    @input="updateValue($event)"
    :disabled="readOnly"
  >
    <option value=""></option>
    <option
      v-for="operator in operatorIsOrWasOptions"
      :key="operator.value"
      :value="operator.value"
    >
      {{ operator.text }}
    </option>
  </IFormSelect>
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
import map from 'lodash/map'

const props = defineProps(propsDefinition)

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

/**
 * Indicates whether the operator is IS
 *
 * @return {Boolean}
 */
const isDateIsOperator = computed(() => props.query.operator === 'is')

/**
 * Get the IS operator options
 *
 * @return {Array}
 */
const isOperatorOptions = computed(() =>
  map(props.rule.operatorsOptions['is'], (option, value) => ({
    value: value,
    text: option,
  }))
)

/**
 * Get the WAS operator options
 *
 * @return {Array}
 */
const wasOperatorOptions = computed(() =>
  map(props.rule.operatorsOptions['was'], (option, value) => ({
    value: value,
    text: option,
  }))
)

/**
 * Get the IS or WAS operator options
 *
 * @return {Array}
 */
const operatorIsOrWasOptions = computed(() =>
  isDateIsOperator.value ? isOperatorOptions.value : wasOperatorOptions.value
)

/**
 * Indicates whether the operator is WAS
 *
 * @return {Boolean}
 */
const isDateWasOperator = computed(() => props.query.operator === 'was')
</script>
