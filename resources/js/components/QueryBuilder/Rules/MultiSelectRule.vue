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
    :multiple="true"
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
import map from 'lodash/map'
import isEqual from 'lodash/isEqual'
import { isValueEmpty } from '@/utils'
import { useI18n } from 'vue-i18n'

const props = defineProps(propsDefinition)

const { t } = useI18n()

const { options, setOptions, getOptions } = useElementOptions()

const selectValue = ref(null)

const placeholder = computed(() => {
  return t('core::filters.placeholders.choose_with_multiple', {
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
  newVal => {
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
  if (isValueEmpty(option)) {
    updateSelectValue([])
  } else {
    updateSelectValue(map(option, props.rule.valueKey))
  }
}

/**
 * Prepare component
 *
 * @param  {Array} list
 */
function prepareComponent(list) {
  setOptions(list)

  if (!isValueEmpty(props.query.value)) {
    setInitialValue()
  }
}

/**
 * Set the select initial internal value
 */
function setInitialValue() {
  if (isValueEmpty(props.query.value)) {
    updateSelectValue([])
  } else {
    updateSelectValue(props.query.value)
  }
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
    selectValue.value = []
    return
  }

  value =
    options.value.filter(
      option => value.indexOf(option[props.rule.valueKey]) > -1
    ) || []

  if (!isEqual(value, selectValue.value)) {
    selectValue.value = value
  }
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
