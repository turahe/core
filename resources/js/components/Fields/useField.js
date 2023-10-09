/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */
import {
  ref,
  unref,
  computed,
  watch,
  nextTick,
  onBeforeMount,
  onBeforeUnmount,
} from 'vue'

import { watchArray } from '@vueuse/core'
import get from 'lodash/get'
import isObject from 'lodash/isObject'
import isEqual from 'lodash/isEqual'
import cloneDeep from 'lodash/cloneDeep'
import { isValueEmpty } from '@/utils'
import { useForm } from './useFieldsForm'

export function useField(value, field, formId, isFloating, options = {}) {
  const form = useForm(formId)

  const clearErrorOnChange = ref(true)
  const realInitialValue = ref(null)
  const label = computed(() => field.value.label)

  const isDirty = computed(() => {
    if (options.isDirty) {
      return unref(options.isDirty)
    }

    // Check for null and "" values
    if (isValueEmpty(value.value) && isValueEmpty(realInitialValue.value)) {
      return false
    }

    return !isEqual(value.value, realInitialValue.value)
  })

  const isReadonly = computed(
    () => field.value.readonly || get(field.value, 'attributes.readonly')
  )

  const fieldId = computed(
    () =>
      (field.value.id || field.value.attribute) +
      (unref(isFloating) ? '-floating' : '')
  )

  const checksForDuplicates = computed(
    () =>
      field.value.checkDuplicatesWith &&
      Object.keys(field.value.checkDuplicatesWith).length > 0
  )

  async function makeDuplicateCheckRequest(query) {
    const { data } = await Innoclapps.request().get(
      field.value.checkDuplicatesWith.url,
      {
        params: {
          q: query,
          ...field.value.checkDuplicatesWith.params,
        },
      }
    )
    return data.length > 0 ? data[0] : null
  }

  function configureValueWatcher() {
    if (Array.isArray(value.value)) {
      watchArray(
        value,
        (newList, oldList) => {
          handleValueChanged(newList)
        },
        { deep: true }
      )
    } else {
      watch(
        value,
        (newVal, oldVal) => {
          // VueJS triggers the watcher when an object is not changed too
          // @link https://github.com/vuejs/vue/issues/2164
          if (
            isObject(newVal) &&
            isObject(oldVal) &&
            JSON.stringify(newVal) === JSON.stringify(oldVal)
          ) {
            return
          }

          handleValueChanged(newVal)
        },
        { deep: true }
      )
    }
  }

  function handleValueChanged(newVal) {
    field.value.currentValue = newVal

    if (clearErrorOnChange.value && form.errors) {
      form.errors.clear(field.value.attribute)
    }

    if (field.value.emitChangeEvent) {
      Innoclapps.$emit(field.value.emitChangeEvent, newVal)
    }
  }

  function initialize() {
    setInitialValue()

    // If not already set in parent component in "created" lifecycle
    if (!realInitialValue.value) {
      realInitialValue.value = cloneDeep(value.value)
    }

    form.set(field.value.attribute, value.value)
  }

  function setInitialValue() {
    if (options.setInitialValue) {
      options.setInitialValue()
    } else {
      value.value = !(
        field.value.value === undefined || field.value.value === null
      )
        ? field.value.value
        : ''
    }

    nextTick(() => {
      field.value.currentValue = value.value
      nextTick(configureValueWatcher)
    })
  }

  function fill(formInstance) {
    if (options.fill) {
      options.fill(formInstance)
      return
    }

    formInstance.fill(field.value.attribute, value.value)
  }

  function handleChange(newValue) {
    if (options.handleChange) {
      options.handleChange(newValue)
      return
    }

    value.value = newValue
    realInitialValue.value = newValue
  }

  onBeforeMount(() => {
    // Add a default fill and handleChange methods for the field
    field.value.fill = fill

    // For form reset and to set initial value
    field.value.handleChange = handleChange

    // Provide dirty check function
    field.value.isDirty = () => {
      return isDirty.value
    }
  })

  onBeforeUnmount(() => {
    // Allows to garbage collect the fields?
    field.value.isDirty = null
    field.value.currentValue = null
    field.value.handleChange = null
    field.value.fill = null
  })

  return {
    field,
    label,
    fieldId,
    realInitialValue,
    isReadonly,
    initialize,
    checksForDuplicates,
    makeDuplicateCheckRequest,
  }
}
