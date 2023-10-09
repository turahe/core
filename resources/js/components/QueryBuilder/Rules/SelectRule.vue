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
  <!--  Todo for multiple check if valueKey and labelKey will work -->
  <ICustomSelect
    :multiple="isMultiSelect"
    v-model="selectValue"
    :disabled="readOnly"
    size="sm"
    :input-id="'rule-' + rule.id + '-' + index"
    :placeholder="placeholder"
    :label="rule.labelKey"
    :options="options"
  />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, toRef, computed, watch } from 'vue'
import { useType } from './useType'
import propsDefinition from './props'
import { useElementOptions } from '~/Core/resources/js/composables/useElementOptions'
import find from 'lodash/find'
import isEqual from 'lodash/isEqual'
import { isValueEmpty } from '@/utils'
import { useI18n } from 'vue-i18n'

const props = defineProps(propsDefinition)

const { t } = useI18n()

const { options, setOptions, getOptions } = useElementOptions()

const selectValue = ref(null)

const isMultiSelect = computed(() => {
  return props.rule.type === 'multi-select'
})

const placeholder = computed(() => {
  return t('core::filters.placeholders.choose', {
    label: props.operand ? props.operand.label : props.rule.label,
  })
})

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

/**
 * Watch the value for change and update actual query value
 */
watch(
  selectValue,
  (newVal, oldVal) => {
    handleChange(newVal)
  },
  { deep: true }
)

/**
 * Handle change for select to update the value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function handleChange(option) {
  let value = null

  if (option && !isValueEmpty(option[props.rule.valueKey])) {
    // Allows zero in the value
    value = option[props.rule.valueKey]
  }

  updateSelectValue(value)
}

/**
 * Prepare component
 *
 * @param  {Array} options
 */
function prepareComponent(list) {
  setOptions(list)

  // First option selected by default
  if (isValueEmpty(props.query.value)) {
    updateSelectValue(options.value[0][props.rule.valueKey])
  } else {
    setInitialValue()
  }
}

/**
 * Set the select initial internal value
 */
function setInitialValue() {
  let value = find(options.value, [props.rule.valueKey, props.query.value])

  updateSelectValue(value ? value[props.rule.valueKey] : null)
}

/**
 * Set the select value from the given query builder value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function setSelectValue(value) {
  if (isValueEmpty(value)) {
    selectValue.value = null
    return
  }

  selectValue.value = find(options.value, [props.rule.valueKey, value]) || null
}

/**
 * Update the current rule query value
 *
 * @param  {String|Array|Number|null} value
 */
function updateSelectValue(value) {
  updateValue(value)

  if (!isEqual(value, selectValue.value)) {
    setSelectValue(value)
  }
}

getOptions(props.rule).then(prepareComponent)
</script>
