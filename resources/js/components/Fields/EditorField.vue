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
    <Editor :disabled="isReadonly" v-model="value" v-bind="field.attributes" />
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import { randomString } from '@/utils'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

/**
 * Note: We do use pass the field id as editor id
 * some fields may have same name e.q. on resource profile and the editor
 * won't be initialized, in this case, custom editor id will be generated automatically
 */
const fieldId = randomString()

onMounted(initialize)
</script>
