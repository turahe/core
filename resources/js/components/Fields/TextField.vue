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
    <FieldFormInputGroup :field="field">
      <IFormInput
        :id="fieldId"
        v-model="value"
        @input="searchDuplicateRecord"
        :disabled="isReadonly"
        :type="field.inputType || 'text'"
        v-bind="field.attributes"
        :class="{
          'pl-11': field.inputGroupPrepend,
          'pr-11': field.inputGroupAppend,
        }"
      />
      <IAlert
        v-if="duplicateRecord"
        dismissible
        @dismissed="duplicateRecord = null"
        class="mt-2"
      >
        <i18n-t
          scope="global"
          :keypath="field.checkDuplicatesWith.lang_keypath"
        >
          <template #display_name>
            <span class="font-medium">
              {{ duplicateRecord.display_name }}
            </span>
          </template>
        </i18n-t>

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <IButtonMinimal
              tag="a"
              :href="duplicateRecord.path"
              rel="noopener noreferrer"
              target="_blank"
              variant="info"
              icon="ExternalLink"
              :text="$t('core::app.view_record')"
            />
          </div>
        </div>
      </IAlert>
    </FieldFormInputGroup>
  </FieldFormGroup>
</template>
<script setup>
import { ref, shallowRef, toRef, onMounted } from 'vue'
import debounce from 'lodash/debounce'
import FieldFormGroup from './FieldFormGroup.vue'
import FieldFormInputGroup from './FieldFormInputGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

const props = defineProps(propsDefinition)

const value = ref('')

const duplicateRecord = shallowRef(null)

const {
  field,
  label,
  fieldId,
  isReadonly,
  initialize,
  checksForDuplicates,
  makeDuplicateCheckRequest,
} = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

/**
 * Search for duplicate record
 */
const searchDuplicateRecord = debounce(() => {
  if (!checksForDuplicates.value || props.view !== 'create' || !value.value) {
    duplicateRecord.value = null
    return
  }

  makeDuplicateCheckRequest(value.value).then(
    duplicate => (duplicateRecord.value = duplicate)
  )
}, 700)

onMounted(initialize)
</script>
