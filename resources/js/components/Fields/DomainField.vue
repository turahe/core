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
    <FieldFormInputGroup :field="field">
      <IFormInput
        :id="fieldId"
        v-model="value"
        @blur="parseDomain"
        :disabled="isReadonly"
        v-bind="field.attributes"
        :class="{
          'pl-11': field.inputGroupPrepend,
          'pr-11': field.inputGroupAppend,
        }"
      />
    </FieldFormInputGroup>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import { parse } from 'tldts'
import FieldFormInputGroup from './FieldFormInputGroup.vue'
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

function parseDomain() {
  // If not valid, domain will be null.
  value.value = parse(value.value).domain
}

onMounted(initialize)
</script>
