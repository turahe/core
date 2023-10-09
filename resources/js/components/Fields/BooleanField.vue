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
  <FieldFormGroup :field="field" :form-id="formId" :field-id="fieldId">
    <IFormCheckbox
      :id="field.name"
      v-model:checked="value"
      :disabled="isReadonly"
      :name="field.name"
      :value="field.trueValue"
      v-bind="field.attributes"
      :unchecked-value="field.falseValue"
    >
      <span v-html="label"></span>
    </IFormCheckbox>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
