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
    :form-id="formId"
    :field-id="fieldId"
  >
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <IFormCheckbox
        v-for="option in options"
        :key="option[field.valueKey]"
        :value="option[field.valueKey]"
        v-model:checked="value"
        :name="field.attribute"
        v-bind="field.attributes"
        :disabled="isReadonly"
        :swatch-color="option.swatch_color"
        :label="option[field.labelKey]"
      />
    </div>
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

const value = ref([])

function setInitialValue() {
  value.value = prepareValue(props.field.value)
}

function handleChange(newValue) {
  value.value = prepareValue(newValue)
  realInitialValue.value = value.value
}

function prepareValue(value) {
  return (!(value === undefined || value === null) ? value : []).map(
    value => value[props.field.valueKey]
  )
}

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
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
