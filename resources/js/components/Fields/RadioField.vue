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
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <IFormRadio
        v-for="option in options"
        :key="option[field.valueKey]"
        :name="field.attribute"
        :value="option[field.valueKey]"
        :id="'radio-' + option[field.valueKey]"
        v-bind="field.attributes"
        v-model="value"
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

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

getOptions(props.field).then(setOptions)

onMounted(initialize)
</script>
