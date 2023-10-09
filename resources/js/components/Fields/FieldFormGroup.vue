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
  <IFormGroup :description="description">
    <a
      v-if="isToggleable"
      href="#"
      @click.prevent="toggleIsVisible = true"
      class="link flex text-sm"
    >
      <Icon icon="Plus" class="mr-1 h-4 w-4" />
      {{ label }}
    </a>

    <div v-show="!isToggleable">
      <IFormLabel
        v-if="label"
        :for="fieldId"
        class="mb-1 inline-flex items-center"
        :required="field.isRequired"
      >
        <Icon
          v-if="displayHelpAsIcon"
          icon="QuestionMarkCircle"
          class="mr-1 h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          v-i-tooltip="field.helpText"
        />
        <span v-html="label"></span>
      </IFormLabel>

      <slot></slot>
    </div>

    <IAlert v-if="displayHelpAsAlert" variant="info" class="mt-3">
      {{ field.helpText }}
    </IAlert>

    <IFormError v-text="form.getError(field.attribute)" />
  </IFormGroup>
</template>
<script setup>
import { ref, computed } from 'vue'
import { isValueEmpty } from '@/utils'
import { useForm } from './useFieldsForm'

const props = defineProps({
  formId: { required: true, type: String },
  field: { required: true, type: Object },
  fieldId: String,
  label: String,
})

const form = useForm(props.formId)
const toggleIsVisible = ref(false)

const isToggleable = computed(
  () => props.field.toggleable && !toggleIsVisible.value && !hasValue.value
)

const displayHelpAsIcon = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'icon'
)

const displayHelpAsText = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'text'
)

const displayHelpAsAlert = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'alert'
)

const hasValue = computed(() => !isValueEmpty(props.field.value))

const description = computed(() =>
  !isToggleable.value && displayHelpAsText.value ? props.field.helpText : null
)
</script>
