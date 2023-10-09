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
    <VisibilityGroupSelector
      v-model:type="value.type"
      v-model:dependsOn="value.depends_on"
      v-bind="field.attributes"
      :disabled="isReadonly"
    />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'
import VisibilityGroupSelector from '~/Core/resources/js/components/VisibilityGroupSelector.vue'

const props = defineProps(propsDefinition)

const value = ref({})

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue,
  }
)

function setInitialValue() {
  if (!props.field.value || !props.field.value.type) {
    value.value = {
      type: 'all',
      depends_on: [],
    }
  } else {
    value.value = props.field.value
  }
}

onMounted(initialize)
</script>
