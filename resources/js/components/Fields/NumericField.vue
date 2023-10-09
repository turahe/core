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
    <FieldFormInputGroup :field="field">
      <IFormNumericInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        :class="{
          'pl-14': field.inputGroupPrepend,
          'pr-14': field.inputGroupAppend,
        }"
        :name="field.attribute"
        v-bind="field.attributes"
      />
    </FieldFormInputGroup>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, nextTick, onMounted } from 'vue'
import FieldFormInputGroup from './FieldFormInputGroup.vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')

function handleChange(newValue) {
  value.value = newValue !== null ? newValue : ''
  realInitialValue.value = value.value
}

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      handleChange,
    }
  )

onMounted(initialize)
</script>
