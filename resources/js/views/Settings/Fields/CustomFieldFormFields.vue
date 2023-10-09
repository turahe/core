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
  <IFormGroup
    :label="$t('core::fields.custom.type')"
    label-for="field_type"
    required
  >
    <ICustomSelect
      :options="fieldsTypes"
      :clearable="false"
      :disabled="edit"
      v-model="form.field_type"
      @option:selected="!form.label ? $refs.labelRef.focus() : ''"
    />
    <IFormError v-text="form.getError('field_type')" />
  </IFormGroup>

  <IFormGroup label-for="label" :label="$t('core::fields.label')" required>
    <IFormInput v-model="form.label" id="label" ref="labelRef" />
    <IFormError v-text="form.getError('label')" />
  </IFormGroup>

  <FieldOptions v-if="isOptionableField" :form="form" />

  <IFormGroup :description="$t('core::fields.custom.id_info')">
    <template #label>
      <div class="flex">
        <IFormLabel
          required
          class="mb-1 grow"
          for="field_id"
          :label="$t('core::fields.custom.id')"
        />

        <IButtonCopy
          v-show="form.field_id"
          :text="form.field_id"
          :success-message="$t('core::app.copied')"
          tabindex="-1"
          class="link cursor-pointer text-sm"
          tag="a"
        >
          {{ $t('core::app.copy') }}
        </IButtonCopy>
      </div>
    </template>

    <div class="relative">
      <div
        v-if="!edit"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm dark:text-white"
      >
        {{ idPrefix }}
      </div>

      <IFormInput
        v-model="fieldId"
        :disabled="edit"
        id="field_id"
        :class="{ 'pl-8': !edit }"
      />
    </div>

    <IFormError v-text="form.getError('field_id')" />
  </IFormGroup>

  <IFormGroup v-if="isUniqueable">
    <IFormCheckbox
      :disabled="edit"
      v-model:checked="form.is_unique"
      :label="$t('core::fields.is_unique')"
    />

    <IFormText v-t="'core::fields.is_unique_change_info'" class="mt-1" />

    <IFormError v-text="form.getError('is_unique')" />
  </IFormGroup>
</template>
<script setup>
import { ref, computed, watch } from 'vue'
import FieldOptions from './CustomFieldFormOptions.vue'
import find from 'lodash/find'
import map from 'lodash/map'
import { watchDebounced } from '@vueuse/core'

const props = defineProps({
  form: { required: true, type: Object },
  edit: { default: false, type: Boolean },
})

const customFields = Innoclapps.config('fields.custom_fields')
const idPrefix = Innoclapps.config('fields.custom_field_prefix')
const resources = Innoclapps.config('resources')
const fieldId = ref(props.form.field_id || null)

const fieldsTypes = computed(() => map(customFields, (field, type) => type))

const isOptionableField = computed(() =>
  Boolean(
    find(customFields, {
      type: props.form.field_type,
      optionable: true,
    })
  )
)

const isUniqueable = computed(() => {
  return (
    Boolean(
      find(customFields, {
        type: props.form.field_type,
        uniqueable: true,
      })
    ) &&
    Boolean(
      find(resources, {
        acceptsUniqueCustomFields: true,
        name: props.form.resource_name,
      })
    )
  )
})

watch(
  isUniqueable,
  newVal => {
    props.form.is_unique = newVal
      ? props.form.is_unique !== null
        ? props.form.is_unique
        : false
      : null
  },
  { immediate: true }
)

watchDebounced(
  fieldId,
  newVal => {
    if (!props.edit) {
      props.form.fill('field_id', newVal ? `${idPrefix}${newVal}` : null)
    }
  },
  { debounce: 250 }
)

watch(isOptionableField, (newVal, oldVal) => {
  if (!newVal && oldVal) {
    delete props.form.options
  } else if (newVal) {
    props.form.options = []
  }
})
</script>
