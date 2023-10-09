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
    <ICustomSelect
      v-model="value"
      :input-id="fieldId"
      :disabled="isReadonly"
      :filterable="filterable"
      :options="options"
      :create-option-provider="createOption"
      :loading="lazyLoadingOptions"
      :name="field.attribute"
      :label="field.labelKey"
      v-bind="field.attributes"
      @search="onSearch"
      @open="onDropdownOpen"
    >
      <template #no-options>{{ noOptionsText }}</template>

      <template #header>
        <span
          v-show="headerText"
          class="block px-3 py-2 text-sm text-neutral-500 dark:text-neutral-200"
        >
          {{ headerText }}
        </span>
        <span
          v-if="lazyLoadingOptions"
          class="-mt-1 block px-3 py-2 text-sm text-neutral-500 dark:text-neutral-200"
        >
          <span class="block h-4 w-4 motion-safe:animate-bounce">...</span>
        </span>
      </template>
    </ICustomSelect>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useSelectField } from './useSelectField'

const props = defineProps(propsDefinition)

const value = ref('')

const {
  field,
  label,
  fieldId,
  isReadonly,

  createOption,
  onSearch,
  onDropdownOpen,
  filterable,
  options,
  lazyLoadingOptions,
  noOptionsText,
  headerText,
} = useSelectField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)
</script>
