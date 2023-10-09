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
  <VDatePicker
    v-model.string.range="localValue"
    :timezone="userTimezone"
    :mode="mode"
    :is-required="required"
    :is-dark="isDarkMode()"
    :locale="locale"
    :is24hr="!usesTwelveHourTime"
    :hide-time-header="true"
    :masks="masks"
    title-position="left"
    :popover="{
      visibility: 'click',
      positionFixed: true,
    }"
  >
    <template v-slot="slotProps">
      <slot
        v-bind="{
          ...slotProps,
          inputValue: localizedRangeValue,
        }"
      >
        <div class="flex flex-col items-center justify-start sm:flex-row">
          <div :class="[roundedClass, 'relative grow shadow-sm']">
            <div
              v-if="withIcon"
              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
              <Icon
                v-once
                icon="Calendar"
                class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
              />
            </div>
            <input
              type="text"
              readonly
              :class="[
                'form-input border-neutral-300 dark:border-neutral-500 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
                roundedClass,
                {
                  'pl-11': withIcon,
                  'form-input-sm': size === 'sm',
                  'form-input-lg': size === 'lg',
                },
              ]"
              autocomplete="off"
              :value="localizedValueRangeStart"
              :placeholder="placeholder"
              v-on="slotProps.inputEvents[rangeKeys.start]"
              :disabled="disabled"
              :name="name + '-' + rangeKeys.start"
              :id="id + '-' + rangeKeys.start"
            />
          </div>
          <span class="m-2 shrink-0">
            <Icon icon="ArrowRight" class="h-4 w-4 text-neutral-600" />
          </span>
          <div :class="[roundedClass, 'relative grow shadow-sm']">
            <div
              v-if="withIcon"
              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
              <Icon
                v-once
                icon="Calendar"
                class="h-5 w-5 text-neutral-400 dark:text-neutral-300"
              />
            </div>
            <input
              type="text"
              readonly
              :class="[
                'form-input border-neutral-300 dark:border-neutral-500 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
                roundedClass,
                {
                  'pl-11': withIcon,
                  'form-input-sm': size === 'sm',
                  'form-input-lg': size === 'lg',
                },
              ]"
              autocomplete="off"
              :value="localizedValueRangeEnd"
              :placeholder="placeholder"
              v-on="slotProps.inputEvents[rangeKeys.end]"
              :disabled="disabled"
              :name="name + '-' + rangeKeys.end"
              :id="id + '-' + rangeKeys.end"
            />
            <Icon
              v-if="clearable"
              v-show="Boolean(rangeStart) && Boolean(rangeEnd)"
              icon="X"
              class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
              @click="clearValues"
            />
          </div>
        </div>
      </slot>
    </template>
  </VDatePicker>
</template>
<script setup>
import { ref, computed, watch } from 'vue'
import { DatePicker as VDatePicker } from 'v-calendar'
import 'v-calendar/dist/style.css'
import { isValueEmpty, isDarkMode } from '@/utils'
import { useApp } from '~/Core/resources/js/composables/useApp'
import { useDates } from '~/Core/resources/js/composables/useDates'

const emit = defineEmits(['update:modelValue', 'input'])

const props = defineProps({
  modelValue: Object,
  withIcon: { default: true, type: Boolean },
  id: String,
  name: String,
  placeholder: String,
  disabled: Boolean,
  required: Boolean,
  clearable: { default: false, type: Boolean },
  rounded: { type: Boolean, default: true },
  rangeKeys: { type: Object, default: () => ({ start: 'start', end: 'end' }) },
  mode: {
    type: String,
    default: 'date',
    validator: value => ['date', 'dateTime', 'time'].includes(value),
  },
  size: {
    type: [String, Boolean],
    default: '',
    validator: value => ['sm', 'lg', 'md', '', false].includes(value),
  },
})

const {
  appMoment,
  localizedDateTime,
  dateToAppTimezone,
  dateFromAppTimezone,
  dateFormatForMoment,
  usesTwelveHourTime,
  userTimezone,
} = useDates()

const { currentUser, setting } = useApp()

const localValue = ref(null)

const roundedClass = computed(() => {
  if (props.rounded && props.size === 'sm') {
    return 'rounded'
  }
  if (props.rounded && props.size !== 'sm' && props.size !== false) {
    return 'rounded-md'
  }
})

const isDateTime = computed(() => {
  return props.mode.toLowerCase() === 'datetime'
})

const isDate = computed(() => {
  return props.mode.toLowerCase() === 'date'
})

const masks = computed(() => {
  let masks = {}

  if (isDate.value) {
    masks.input = dateFormatForMoment.value
    masks.modelValue = 'YYYY-MM-DD'
  } else if (isDateTime.value) {
    masks.modelValue = 'YYYY-MM-DD HH:mm:ss'
  } else {
    // TODO time, not yet used
  }

  return masks
})

const localizedRangeValue = computed(() => {
  return {
    [props.rangeKeys.start]: localizedValueRangeStart.value,
    [props.rangeKeys.end]: localizedValueRangeEnd.value,
  }
})

const rangeStart = computed(() => {
  return localValue.value[props.rangeKeys.start]
})

const rangeEnd = computed(() => {
  return localValue.value[props.rangeKeys.end]
})

const localizedValueRangeStart = computed(() => {
  return rangeStart.value ? localizeValue(rangeStart.value) : ''
})

const localizedValueRangeEnd = computed(() => {
  return rangeEnd.value ? localizeValue(rangeEnd.value) : ''
})

const locale = computed(() => {
  let firstDayOfWeek = Number(
    currentUser
      ? currentUser.value.first_day_of_week
      : setting('first_day_of_week')
  )

  return {
    masks: masks.value,
    id: navigator.language,
    firstDayOfWeek: firstDayOfWeek + 1, // uses 1-7 not 0-6 weekdays
  }
})

watch(localValue, newVal => {
  if (isValueEmpty(newVal)) {
    return emitEmptyValChangeEvent()
  }

  if (isDate.value) {
    emitValChangeEvent(newVal)
  } else if (isDateTime.value) {
    emitValChangeEvent({
      [props.rangeKeys.start]: dateToAppTimezone(newVal[props.rangeKeys.start]),
      [props.rangeKeys.end]: dateToAppTimezone(newVal[props.rangeKeys.end]),
    })
  } else {
    // TODO time, not yet used
    emitValChangeEvent(newVal)
  }
})

watch(
  () => props.modelValue,
  newVal => {
    if (isEqualToLocalValue(newVal)) {
      return
    }

    setLocalValueFromModelValue(newVal)
  },
  { immediate: true, deep: true }
)

function localizeValue(value) {
  if (isDate.value) {
    return appMoment(value).format(dateFormatForMoment.value)
  } else if (isDateTime.value) {
    return localizedDateTime(dateToAppTimezone(value))
  } else {
    // TODO time, not yet used
    return value
  }
}

function setLocalValueFromModelValue(value) {
  if (isDate.value) {
    localValue.value = {
      [props.rangeKeys.start]: value[props.rangeKeys.start],
      [props.rangeKeys.end]: value[props.rangeKeys.end],
    }
  } else if (isDateTime.value) {
    localValue.value = {
      [props.rangeKeys.start]: dateFromAppTimezone(
        value[props.rangeKeys.start]
      ),
      [props.rangeKeys.end]: dateFromAppTimezone(value[props.rangeKeys.end]),
    }
  } else {
    // TODO time, not yet used
    localValue.value = value
  }
}

function isEqualToLocalValue(value) {
  if (value === localValue.value) {
    return true
  }

  if ((!localValue.value && value) || (!value && localValue.value)) {
    return false
  }

  if (isDateTime.value) {
    return (
      dateFromAppTimezone(value[props.rangeKeys.start] === rangeStart.value) &&
      dateFromAppTimezone(value[props.rangeKeys.end] === rangeEnd.value)
    )
  } else if (isDate.value) {
    return (
      value[props.rangeKeys.start] === rangeStart.value &&
      value[props.rangeKeys.end] === rangeEnd.value
    )
  } else {
    // TODO time, not yet used
  }
}

function clearValues() {
  localValue.value[props.rangeKeys.start] = null
  localValue.value[props.rangeKeys.end] = null
}

function emitValChangeEvent(value) {
  emit('update:modelValue', value)
  emit('input', value)
}

function emitEmptyValChangeEvent() {
  emitValChangeEvent({
    [props.rangeKeys.start]: null,
    [props.rangeKeys.end]: null,
  })
}
</script>
<style>
.vc-time-picker select {
  border: 0;
}
</style>
