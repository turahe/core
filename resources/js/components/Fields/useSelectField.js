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
import { ref, unref, computed, watch, nextTick } from 'vue'
import { useElementOptions } from '~/Core/resources/js/composables/useElementOptions'
import { isValueEmpty } from '@/utils'
import isFunction from 'lodash/isFunction'
import debounce from 'lodash/debounce'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import isEqual from 'lodash/isEqual'
import isObject from 'lodash/isObject'
import { useI18n } from 'vue-i18n'
import { useField } from './useField'

export function useSelectField(
  value,
  field,
  formId,
  isFloating,
  selectOptions = {}
) {
  const { t } = useI18n()
  const { options, setOptions, getOptions } = useElementOptions()

  const performedAsyncSearch = ref(false)
  const minimumAsyncCharacters = ref(2)
  const totalAsyncSearchCharacters = ref(0)
  const minimumAsyncCharactersRequirement = ref(false)
  const lazyLoadingOptions = ref(false)
  const lazyLoadedOptions = ref(null)

  const isAsync = computed(() => Boolean(field.value.asyncUrl))

  const filterable = computed(() => (isAsync.value ? false : true))

  const headerText = computed(() => {
    // Only for async
    if (isAsync.value && minimumAsyncCharactersRequirement.value) {
      return t('core::app.type_more_to_search', {
        characters:
          minimumAsyncCharacters.value - totalAsyncSearchCharacters.value,
      })
    }
  })

  const noOptionsText = computed(() => {
    if (!isAsync.value || (isAsync.value && performedAsyncSearch.value)) {
      return t('core::app.no_search_results')
    }

    // This is shown only the first time user clicked on the select (only for async)
    return t('core::app.type_to_search')
  })

  const isDirty = computed(() => {
    if (selectOptions.isDirty) {
      if (isFunction(selectOptions.isDirty)) {
        return selectOptions.isDirty(value, realInitialValue)
      }

      return unref(selectOptions.isDirty)
    }
    // Check for null and "" values
    if (isValueEmpty(value.value) && isValueEmpty(realInitialValue.value)) {
      return false
    }

    if (isValueEmpty(value.value)) {
      return !isValueEmpty(realInitialValue.value)
    } else if (isValueEmpty(realInitialValue.value)) {
      return !isValueEmpty(value.value)
    } else if (!isObject(realInitialValue.value)) {
      return !isEqual(value.value[field.value.valueKey], realInitialValue.value)
    }

    return !isEqual(
      value.value[field.value.valueKey],
      realInitialValue.value[field.value.valueKey]
    )
  })

  function onSearch(search, loading) {
    // Regular search is performed via the select field when it's not async.
    if (!isAsync.value) {
      return
    }

    asyncSearch(search, loading)
  }

  const asyncSearch = debounce(function (q, loading) {
    if (q == '') {
      options.value =
        lazyLoadedOptions.value !== null ? lazyLoadedOptions.value : []
      return
    }

    const totalCharacters = q.length

    totalAsyncSearchCharacters.value = totalCharacters

    if (filterable.value || totalCharacters < minimumAsyncCharacters.value) {
      minimumAsyncCharactersRequirement.value = true

      return q
    }

    minimumAsyncCharactersRequirement.value = false

    retrieveOptions(q, loading)
  }, 400)

  async function retrieveOptions(q, loading) {
    loading(true)

    let { data } = await Innoclapps.request().get(field.value.asyncUrl, {
      params: {
        q: q,
      },
    })

    options.value = data
    performedAsyncSearch.value = true

    loading(false)
  }

  function setInitialValue() {
    value.value = prepareValue(field.value.value)
  }

  function handleChange(val) {
    let prepared = prepareValue(val)
    value.value = prepared
    realInitialValue.value = cloneDeep(prepared)
  }

  function prepareValue(value) {
    // Has value and the value is not object
    // But the options are multi dimensional array
    // In this case, we must set the value as object so it can be populated within the select
    if (
      value &&
      typeof value != 'object' &&
      options.value.length > 0 &&
      typeof options.value[0] === 'object'
    ) {
      return find(options.value, [field.value.valueKey, value])
    } else {
      return value
    }
  }

  function onDropdownOpen() {
    if (!field.value.lazyLoad || lazyLoadedOptions.value !== null) {
      return
    }

    lazyLoadingOptions.value = true

    Innoclapps.request()
      .get(field.value.lazyLoad.url, {
        params: field.value.lazyLoad.params,
      })
      .then(({ data }) => {
        options.value = data?.data || data
        lazyLoadedOptions.value = options.value
      })
      .finally(() => (lazyLoadingOptions.value = false))
  }

  function createOption(newOption) {
    return {
      [field.value.valueKey]: newOption,
      [field.value.labelKey]: newOption,
    }
  }

  function fill(form) {
    if (selectOptions.fill) {
      selectOptions.fill(form)
      return
    }

    form.fill(
      field.value.attribute,
      (value.value && value.value[field.value.valueKey]) || null
    )
  }

  function configureValueWatcher() {
    watch(
      () => field.value.value,
      newVal => handleChange(newVal)
    )
  }

  getOptions(selectOptions.options || field.value).then(opts =>
    setOptions(opts, () => {
      initialize()
      nextTick(configureValueWatcher)
    })
  )

  const { label, fieldId, isReadonly, realInitialValue, initialize } = useField(
    value,
    field,
    formId,
    isFloating,
    {
      fill,
      isDirty,
      setInitialValue,
      handleChange,
    }
  )

  return {
    createOption,
    onSearch,
    onDropdownOpen,
    filterable,
    setOptions,
    options,
    lazyLoadingOptions,
    noOptionsText,
    headerText,
    prepareValue,

    field,
    label,
    fieldId,
    isReadonly,
  }
}
