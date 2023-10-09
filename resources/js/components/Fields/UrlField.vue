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
    <div class="relative">
      <IFormInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        type="url"
        v-bind="field.attributes"
        class="pr-10"
      />
      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <a
          :href="value || '#'"
          :class="[
            !value ? 'text-neutral-400 dark:text-neutral-300' : 'link',
            { 'pointer-events-none': !value },
          ]"
        >
          <Icon icon="Link" class="h-5 w-5" />
        </a>
      </div>
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

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
