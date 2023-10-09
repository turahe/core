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
    <TimezoneInput
      v-model="value"
      v-bind="field.attributes"
      :field-id="fieldId"
      :clearable="true"
      :name="field.attribute"
      :disabled="isReadonly"
    />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import TimezoneInput from '~/Core/resources/js/components/TimezoneInput.vue'
import propsDefinition from './props'
import { useField } from './useField'
import { useStore } from 'vuex'

const props = defineProps(propsDefinition)

const store = useStore()

const value = ref('')

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
// Set the timezones from the field options in store, so the TimezoneInput won't make request
// to actually retrieve the timezones from storage, e.q. useful also on web forms
store.commit('SET_TIMEZONES', props.field.timezones)
</script>
