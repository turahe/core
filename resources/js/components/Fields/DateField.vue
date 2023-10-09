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
import { useDates } from '~/Core/resources/js/composables/useDates'

const props = defineProps(propsDefinition)

const { appMoment } = useDates()
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

    return moment(d1).isSame(d2)
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

function handleChange(newValue) {
  value.value = newValue ? appMoment(newValue).format('YYYY-MM-DD') : null
  realInitialValue.value = value.value
}

function setInitialValue() {
  value.value = props.field.value
    ? appMoment(props.field.value).format('YYYY-MM-DD')
    : null
}

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      isDirty,
      setInitialValue,
      handleChange,
    }
  )

realInitialValue.value = props.field.value
  ? appMoment(props.field.value).format('YYYY-MM-DD')
  : null

onMounted(initialize)
</script>
