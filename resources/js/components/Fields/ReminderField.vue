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
    <div class="flex items-center">
      <div class="mr-2">
        <div class="flex rounded-md shadow-sm">
          <IButtonClose
            v-if="field.cancelable"
            :rounded="false"
            @click="cancelReminder()"
            :icon="!cancelled ? 'X' : 'Bell'"
            variant="white"
            class="relative -mr-px rounded-l-md focus:z-20"
          />
          <div class="relative flex grow items-stretch">
            <IFormNumericInput
              type="number"
              :max="maxAttribute"
              :min="1"
              :rounded="false"
              :class="[
                {
                  'rounded-r-md': field.cancelable,
                  'rounded-md': !field.cancelable,
                },
              ]"
              :disabled="cancelled"
              :precision="0"
              :placeholder="$t('core::dates.' + selectedType)"
              v-model="reminderValue"
            />
          </div>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <IFormSelect
          v-model="selectedType"
          :disabled="cancelled"
          class="sm:flex-1"
        >
          <option
            v-for="reminderType in types"
            :key="reminderType"
            :value="reminderType"
          >
            {{ $t('core::dates.' + reminderType) }}
          </option>
        </IFormSelect>
        <div
          class="ml-2 truncate text-neutral-800 dark:text-neutral-300"
          v-t="'core::app.reminder_before_due'"
        />
      </div>
    </div>
  </FieldFormGroup>
</template>
<script setup>
import { ref, toRef, computed, watch, onMounted } from 'vue'
import FieldFormGroup from './FieldFormGroup.vue'
import propsDefinition from './props'
import { useField } from './useField'

import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from '@/utils'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, fieldId, realInitialValue, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue,
    handleChange,
  }
)

const types = ['minutes', 'hours', 'days', 'weeks']
const reminderValue = ref(Innoclapps.config('defaults.reminder_minutes'))
const selectedType = ref('minutes')
const cancelled = ref(false)

/**
 * Get the actual value in minutes
 */
const valueInMinutes = computed(() => {
  if (cancelled.value) {
    return null
  }

  if (selectedType.value === 'minutes') {
    return parseInt(reminderValue.value)
  } else if (selectedType.value === 'hours') {
    return parseInt(reminderValue.value) * 60
  } else if (selectedType.value === 'days') {
    return parseInt(reminderValue.value) * 1440
  } else if (selectedType.value === 'weeks') {
    return parseInt(reminderValue.value) * 10080
  }
})

/**
 * Max attribute for the field
 *
 * @return {Number}
 */
const maxAttribute = computed(() => {
  if (selectedType.value === 'minutes') {
    return 59
  } else if (selectedType.value === 'hours') {
    return 23
  } else if (selectedType.value === 'days') {
    return 6
  }
  // For weeks, as Google allow max 4 weeks reminder
  return 4
})

watch(valueInMinutes, newVal => {
  value.value = newVal
})

/**
 * Set/toggle the no reminder option
 */
function cancelReminder(force) {
  cancelled.value = force === undefined ? !cancelled.value : force
  reminderValue.value = Innoclapps.config('defaults.reminder_minutes')
  selectedType.value = 'minutes'
}

/**
 * Handle field change, update the actual value to proper format
 *
 * @param  {String} value
 *
 * @return {Void}
 */
function handleChange(newValue) {
  if (newValue) {
    value.value = newValue
    reminderValue.value = determineReminderValueBasedOnMinutes(newValue)
    selectedType.value = determineReminderTypeBasedOnMinutes(newValue)
  } else if (newValue === null && props.field.cancelable) {
    cancelReminder(true)
  } else {
    value.value = reminderValue.value
  }

  realInitialValue.value = value.value
}

/*
 * Set the initial value for the field
 */
function setInitialValue() {
  if (props.field.value) {
    value.value = props.field.value

    reminderValue.value = determineReminderValueBasedOnMinutes(
      props.field.value
    )

    selectedType.value = determineReminderTypeBasedOnMinutes(props.field.value)
  } else if (props.field.value === null && props.field.cancelable) {
    cancelReminder()
  } else {
    value.value = reminderValue.value
  }
}

onMounted(initialize)
</script>
