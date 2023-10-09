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
    <DatePicker
      v-model="value"
      :required="field.isRequired"
      :id="fieldId"
      :name="field.attribute"
      :disabled="isReadonly"
      v-bind="field.attributes"
      mode="dateTime"
    />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, computed, onMounted } from 'vue'
import { isValueEmpty } from '@/utils'
import isObject from 'lodash/isObject'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')

const isDirty = computed(() => {
  const areDatesEqual = (d1, d2) => {
    if (isValueEmpty(d1) && isValueEmpty(d2)) {
      return true
    } else if (
      (isValueEmpty(d1) && !isValueEmpty(d2)) ||
      (isValueEmpty(d2) && !isValueEmpty(d1))
    ) {
      return false
    }

    return moment.utc(d1).isSame(d2)
  }

  // Range
  if (isObject(realInitialValue.value) && isObject(value.value)) {
    const keys = Object.keys(value.value)

    return keys.some(
      key => !areDatesEqual(value.value[key], realInitialValue.value[key])
    )
  }

  return !areDatesEqual(value.value, realInitialValue.value)
})

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      isDirty,
    }
  )

onMounted(initialize)
</script>
