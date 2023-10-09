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
    <div>
      <IColorSwatches
        :swatches="swatches"
        :allow-remove="!field.isRequired"
        v-model="value"
      />
    </div>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')
const swatches = Innoclapps.config('favourite_colors')

const { field, label, fieldId, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
