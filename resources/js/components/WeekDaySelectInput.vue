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
  <ICustomSelect
    :input-id="inputId"
    :options="options"
    :clearable="false"
    :reduce="option => option.id"
    v-model="internalValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  />
</template>
<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

const { t } = useI18n()

const weekDays = [
  {
    id: 1,
    label: t('core::app.weekdays.monday'),
  },
  {
    id: 2,
    label: t('core::app.weekdays.tuesday'),
  },
  {
    id: 3,
    label: t('core::app.weekdays.wednesday'),
  },
  {
    id: 4,
    label: t('core::app.weekdays.thursday'),
  },
  {
    id: 5,
    label: t('core::app.weekdays.friday'),
  },
  {
    id: 6,
    label: t('core::app.weekdays.saturday'),
  },
  {
    id: 0,
    label: t('core::app.weekdays.sunday'),
  },
]

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: null,
  inputId: { type: String, default: () => 'first_day_of_week' },
  only: Array,
})

const internalValue = ref(null)

const options = computed(() => {
  if (!props.only) {
    return weekDays
  }

  return weekDays.filter(day => props.only.indexOf(day.id) > -1)
})

watch(
  () => props.modelValue,
  newVal => {
    internalValue.value = find(options.value, ['id', Number(newVal)])
  },
  { immediate: true }
)
</script>
