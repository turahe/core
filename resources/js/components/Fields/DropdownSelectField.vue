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
  <FieldFormGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <DropdownSelectInput
      :modelValue="value"
      @change="value = $event[field.valueKey]"
      :items="options"
      v-bind="field.attributes"
      :label-key="field.labelKey"
      :value-key="field.valueKey"
    />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'
import { useElementOptions } from '~/Core/resources/js/composables/useElementOptions'

const props = defineProps(propsDefinition)

const { options, setOptions, getOptions } = useElementOptions()

const value = ref('')

function setInitialValue() {
  value.value = isObject(props.field.value)
    ? props.field.value[props.field.valueKey] || ''
    : props.field.value || ''
}

function handleChange(newValue) {
  value.value = isObject(newValue)
    ? newValue[props.field.valueKey] || ''
    : newValue || ''
  realInitialValue.value = value.value
}

const { field, label, fieldId, realInitialValue, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue,
    handleChange,
  }
)

getOptions(props.field).then(setOptions)

onMounted(initialize)
</script>
