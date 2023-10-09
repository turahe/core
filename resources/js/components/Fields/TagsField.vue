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
    <TagsSelectInput
      v-model="value"
      :disabled="isReadonly"
      :input-id="fieldId"
      :name="field.attribute"
      :type="field.type"
      v-bind="field.attributes"
    />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import { useField } from './useField'
import propsDefinition from './props'
import TagsSelectInput from '../Tags/TagsSelectInput.vue'

const props = defineProps(propsDefinition)

const value = ref([])

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
