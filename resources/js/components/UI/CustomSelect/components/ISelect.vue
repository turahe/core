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
  <div
    v-bind="$attrs"
    role="button"
    ref="reference"
    @click="open ? hide() : show()"
    :class="[
      {
        'border-primary-500 ring-1 ring-primary-500':
          open && bordered && !simple,
        'px-2.5 py-1.5': size === 'sm',
        'px-3 py-2': (size === 'md' || size === '') && !simple,
        'px-4 py-2.5': size === 'lg',
        'border border-neutral-300 dark:border-neutral-600':
          bordered && !simple,
        rounded: rounded && size === 'sm' && !simple,
        'rounded-md': rounded && size !== 'sm' && size !== false && !simple,
        'shadow-sm': shadow && !simple,
        'bg-white': !simple && !disabled,
        'dark:bg-neutral-700': !simple,
        'bg-neutral-200': !simple && disabled,
        'pointer-events-none': disabled,
      },
      'relative w-full text-left text-sm',
    ]"
  >
    <div class="flex items-center">
      <div
        :id="`cs${uid}__combobox`"
        :class="['flex items-center', { 'cursor-text': isSearchable }]"
        role="combobox"
        :aria-expanded="dropdownOpen.toString()"
        :aria-owns="`cs${uid}__listbox`"
        aria-label="Search for an option"
      >
        <div
          v-show="!search || multiple"
          :class="{
            'absolute left-auto flex opacity-40': !multiple && dropdownOpen,
          }"
        >
          <slot
            v-for="option in selectedValue"
            name="selected-option-container"
            :option="normalizeOptionForSlot(option)"
            :deselect="deselect"
            :multiple="multiple"
            :disabled="disabled"
          >
            <ISelectSelectedOption
              :option="option"
              :simple="simple"
              :get-option-label="getOptionLabel"
              :get-option-key="getOptionKey"
              :normalize-option-for-slot="normalizeOptionForSlot"
              :multiple="multiple"
              :searching="searching"
              :disabled="disabled"
              :deselect="deselect"
            >
              <template #option="slotProps">
                <slot name="selected-option" v-bind="slotProps">
                  {{ slotProps.optionLabel }}
                </slot>
              </template>
            </ISelectSelectedOption>
          </slot>
        </div>
      </div>
      <div class="mr-4 grow" v-show="!simple">
        <input
          :class="[
            (dropdownOpen || isValueEmpty || searching) && !simple
              ? '!w-full'
              : '!w-0',
            'max-w-full border-0 p-0 text-sm leading-none focus:border-0 focus:ring-0 disabled:bg-neutral-200 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
          ]"
          ref="inputSearchRef"
          v-bind="{
            disabled: disabled,
            placeholder: searchPlaceholder,
            tabindex: tabindex,
            readonly: !isSearchable,
            class: 'cs__search',
            id: inputId,
            'aria-autocomplete': 'list',
            'aria-labelledby': `cs${uid}__combobox`,
            'aria-controls': `cs${uid}__listbox`,
            type: 'search',
            autocomplete: autocomplete,
            value: search,
            ...(dropdownOpen && filteredOptions[typeAheadPointer]
              ? {
                  'aria-activedescendant': `cs${uid}__option-${typeAheadPointer}`,
                }
              : {}),
          }"
          @blur="onSearchBlur"
          @focus="onSearchFocus"
          @input="search = $event.target.value"
          @compositionstart="isComposing = true"
          @compositionend="isComposing = true"
          @keydown.delete="maybeDeleteValue"
          @keydown.esc="onEscape"
          @keydown.prevent.up="typeAheadUp"
          @keydown.prevent.down="typeAheadDown"
          @keydown.prevent.enter="!isComposing && typeAheadSelect()"
        />
      </div>
      <div class="inline-flex">
        <button
          type="button"
          v-show="showClearButton"
          :disabled="disabled"
          @click.prevent.stop="clearSelection"
          class="mr-1 text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
          title="Clear Selected"
          aria-label="Clear Selected"
          ref="clearButton"
        >
          <Icon
            icon="X"
            :class="[
              'shrink-0 text-neutral-400',
              size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4',
            ]"
          />
        </button>

        <slot name="spinner" v-bind="{ loading: mutableLoading }">
          <ISpinner
            v-if="mutableLoading"
            :class="[
              'mr-2 shrink-0 text-neutral-400',
              size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4',
            ]"
          />
        </slot>

        <div v-if="!simple || !(simple && disabled)">
          <Icon
            :icon="toggleIcon"
            :class="['text-neutral-400', size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4']"
          />
        </div>
      </div>
    </div>
  </div>

  <Teleport :to="teleportTo">
    <div
      v-if="open"
      ref="dropdownMenuWrapperRef"
      :class="[
        'c-popper overflow-hidden rounded-md border border-neutral-200 bg-white shadow-lg dark:border-neutral-600 dark:bg-neutral-800',
        listWrapperClass,
      ]"
      :style="{
        zIndex: 99999,
        width:
          typeof referenceElWidth === 'number' ? `${referenceElWidth}px` : '',
        ...floatingStyles,
      }"
    >
      <div
        v-if="simple && searchable"
        class="border-b border-neutral-200 px-4 py-3 dark:border-neutral-600"
      >
        <IFormInput
          type="search"
          :placeholder="searchPlaceholder"
          v-model="search"
        />
      </div>
      <slot name="header"></slot>

      <draggable
        ref="dropdownMenuRef"
        :model-value="filteredOptions"
        @update:modelValue="$emit('update:draggable', $event)"
        v-bind="{ ...draggableOptions, ...{ ghostClass: 'drag-ghost' } }"
        handle=".select-option-draggable-handle"
        tag="ul"
        :item-key="item => getOptionKey(item)"
        :id="`cs${uid}__listbox`"
        :key="`cs${uid}__listbox`"
        :class="[listClass, 'max-h-80 overflow-y-auto']"
        role="listbox"
      >
        <template #header v-if="totalFilteredOptions === 0 && !loading">
          <li
            class="relative cursor-default select-none px-4 py-2 text-sm text-neutral-600 dark:text-neutral-300"
          >
            <slot
              name="no-options"
              v-bind="{
                search: search,
                loading: loading,
                searching: searching,
                text: noOptionsText,
              }"
            >
              {{ noOptionsText }}
            </slot>
          </li>
        </template>
        <!-- using v-memo as it's causing all options to re-render on update because of the @mouseover event -->
        <template #item="{ element: option, index }">
          <ISelectOption
            v-memo="[
              index === typeAheadPointer,
              isOptionSelected(option),
              getOptionLabel(option),
              getOptionKey(option),
            ]"
            :key="index"
            @selected="select(option)"
            @type-ahead-pointer="typeAheadPointer = $event"
            :uid="uid"
            :swatch-color="option.swatch_color"
            :index="index"
            :active="index === typeAheadPointer"
            :label="getOptionLabel(option)"
            :is-selectable="selectable(option)"
            :is-selected="isOptionSelected(option)"
          >
            <template v-slot="{ label }">
              <slot
                name="option"
                v-bind="{
                  ...normalizeOptionForSlot(option),
                  ...{ label: label },
                }"
              >
                {{ label }}
              </slot>
            </template>

            <template #option-inner="innerOptionSlotProps">
              <span
                class="absolute right-5 top-2.5 flex space-x-3"
                v-show="filteredOptions.length > 1"
              >
                <slot name="option-actions" v-bind="innerOptionSlotProps" />
                <Icon
                  v-if="reorderable && !searching"
                  icon="Selector"
                  class="select-option-draggable-handle h-4 w-4 cursor-move text-neutral-600 dark:text-neutral-200"
                />
              </span>
            </template>
          </ISelectOption>
        </template>
      </draggable>
      <slot name="footer"></slot>
    </div>
  </Teleport>
</template>
<script>
export default {
  name: 'ICustomSelect',
  inheritAttrs: false,
}
</script>
<script setup>
// TODO, https://vueuse.org/integrations/useFocusTrap/
import { watch, ref, computed, toRef, onMounted, onBeforeUnmount } from 'vue'
import uniqueId from '../utility/uniqueId'
import ISelectOption from './ISelectOption.vue'
import ISelectSelectedOption from './ISelectSelectedOption.vue'
import propsDefinition from '../props'
import { useI18n } from 'vue-i18n'
import { useTypeAheadPointer } from '../useTypeAheadPointer'
import { useFloating, offset, flip, autoUpdate } from '@floating-ui/vue'
import { onClickOutside } from '@vueuse/core'
import draggable from 'vuedraggable'
import { useDraggable } from '~/Core/resources/js/composables/useDraggable'

const emit = defineEmits([
  'update:modelValue',
  'update:draggable',
  'open',
  'close',
  'cleared',
  'option:selecting',
  'option:created',
  'option:selected',
  'option:deselecting',
  'option:deselected',
  'search',
  'search:blur',
  'search:focus',
])

const props = defineProps(propsDefinition)

const { draggableOptions } = useDraggable()

const reference = ref(null)
const dropdownMenuRef = ref(null)
const dropdownMenuWrapperRef = ref(null)
const inputSearchRef = ref(null)
const teleportTo = ref('body')

const uid = uniqueId()
const search = ref('')
const open = ref(false)
const isComposing = ref(false)
const pushedTags = ref([])

// Internal value managed if no `modelValue` prop is passed
const _value = ref([])

const mutableLoading = ref(props.loading)

const { floatingStyles } = useFloating(reference, dropdownMenuWrapperRef, {
  placement: 'bottom',
  middleware: [offset(10), flip()],
  whileElementsMounted: autoUpdate,
})

const { t } = useI18n()

const isSearchable = computed(() => props.searchable && !props.simple)

/**
 * Toggle props.loading. Optionally pass a boolean
 * value. If no value is provided, props.loading
 * will be set to the opposite of it's current value.
 */
function toggleLoading(toggle = null) {
  if (toggle == null) {
    return (mutableLoading.value = !mutableLoading.value)
  }

  return (mutableLoading.value = toggle)
}

/**
 * Local create option function
 */
function createOption(option) {
  let newOption = null
  if (props.createOptionProvider) {
    newOption = props.createOptionProvider(option)
  } else {
    newOption =
      typeof optionList.value[0] === 'object'
        ? { [props.label]: option }
        : option
  }

  emit('option:created', newOption)

  return newOption
}

/**
 * Callback to filter results when search text is provided.
 * Default implementation loops each option, and returns the result of "filterBy".
 */
function filter(options, search) {
  return options.filter(option => {
    let label = getOptionLabel(option)
    if (typeof label === 'number') {
      label = label.toString()
    }
    return props.filterBy(option, label, search)
  })
}

/**
 * Callback to generate the label text. If {option}
 * is an object, returns option[props.label] by default.
 *
 * Label text is used for filtering comparison and
 * displaying. If you only need to adjust the
 * display, you should use the `option` and
 * `selected-option` slots.
 */
function getOptionLabel(option) {
  if (props.optionLabelProvider) {
    return props.optionLabelProvider(option)
  }

  if (typeof option === 'object') {
    if (!option.hasOwnProperty(props.label)) {
      return console.warn(
        `[select warn]: Label key "option.${props.label}" does not` +
          ` exist in options object ${JSON.stringify(option)}.`
      )
    }

    return option[props.label]
  }

  return option
}

/**
 * Generate a unique identifier for each option. If `option`
 * is an object and `option.hasOwnProperty('id')` exists,
 * `option.id` is used by default, otherwise the option
 * will be serialized to JSON.
 *
 * If you are supplying a lot of options, you should
 * provide your own keys, as JSON.stringify can be
 * slow with lots of objects.
 *
 * The result of this function *must* be unique.
 */
function getOptionKey(option) {
  if (props.getOptionKeyProvider) {
    return props.getOptionKeyProvider(option)
  }

  if (typeof option !== 'object') {
    return option
  }

  try {
    return option.hasOwnProperty('id') ? option.id : sortAndStringify(option)
  } catch (e) {
    const warning =
      `[select warn]: Could not stringify this option ` +
      `to generate unique key. Please provide'getOptionKey' prop ` +
      `to return a unique key for each option.`
    return console.warn(warning, option, e)
  }
}

function sortAndStringify(sortable) {
  const ordered = {}

  Object.keys(sortable)
    .sort()
    .forEach(key => {
      ordered[key] = sortable[key]
    })

  return JSON.stringify(ordered)
}

/**
 * Make sure tracked value is one option if possible.
 */
function setInternalValueFromOptions(value) {
  if (Array.isArray(value)) {
    _value.value = value.map(val => findOptionFromReducedValue(val))
  } else {
    _value.value = findOptionFromReducedValue(value)
  }
}

/**
 * Select a given option.
 */
function select(option) {
  emit('option:selecting', option)

  if (!isOptionSelected(option)) {
    if (props.taggable && !optionExists(option)) {
      emit('option:created', option)
      pushTag(option)
    }

    if (props.multiple) {
      option = selectedValue.value.concat(option)
    }

    updateValue(option)
    emit('option:selected', option)
  }

  onAfterSelect(option)
}

/**
 * De-select a given option.
 */
function deselect(option) {
  emit('option:deselecting', option)

  updateValue(selectedValue.value.filter(val => !optionComparator(val, option)))

  emit('option:deselected', option)
}

/**
 * Clears the currently selected value(s)
 */
function clearSelection() {
  updateValue(props.multiple ? [] : null)
  emit('cleared')
}

/**
 * Called from "select" after each selection.
 */
function onAfterSelect(option) {
  if (props.closeOnSelect) {
    hide()
    // this.open = !this.open
    inputSearchRef.value.blur()
  }

  if (props.clearSearchOnSelect) {
    search.value = ''
  }
}

/**
 * Accepts a selected value, updates local state when required, and triggers the input event.
 */
function updateValue(value) {
  if (typeof props.modelValue === 'undefined') {
    // Vue select has to manage value
    _value.value = value
  }

  if (value !== null) {
    if (Array.isArray(value)) {
      value = value.map(val => props.reduce(val))
    } else {
      value = props.reduce(value)
    }
  }

  emit('update:modelValue', value)
}

function focus() {
  inputSearchRef.value.focus()
}

/**
 * Handle the dropdown shown event
 * We need to focus the search element when the dropdown is invoked via the toggle e.q. clicked not on the search input
 */
function show() {
  open.value = true
  focus()
}

/**
 * Handle the dropdown hidden event
 */
function hide() {
  open.value = false
}

/**
 * Check if the given option is currently selected.
 */
function isOptionSelected(option) {
  return selectedValue.value.some(value => optionComparator(value, option))
}

/**
 * Determine if two option objects are matching.
 */
function optionComparator(a, b) {
  if (props.optionComparatorProvider) {
    return props.optionComparatorProvider(a, b, getOptionKey)
  }
  return getOptionKey(a) === getOptionKey(b)
}

/**
 * Finds an option from the options where a reduced value matches the passed in value.
 */
function findOptionFromReducedValue(value) {
  const predicate = option =>
    JSON.stringify(props.reduce(option)) === JSON.stringify(value)

  const matches = [...props.options, ...pushedTags.value].filter(predicate)

  if (matches.length === 1) {
    return matches[0]
  }

  /**
   * This second loop is needed to cover an edge case where `taggable` + `reduce`
   * were used in conjunction with a `create-option` that doesn't create a
   * unique reduced value.
   *
   * @see https://github.com/sagalbot/vue-select/issues/1089#issuecomment-597238735
   */
  return matches.find(match => optionComparator(match, _value.value)) || value
}

/**
 * Delete the value on Delete keypress when there is no text in the search input, & there's tags to delete
 */
function maybeDeleteValue() {
  if (
    !inputSearchRef.value.value.length &&
    selectedValue.value &&
    selectedValue.value.length &&
    props.clearable
  ) {
    let value = null

    if (props.multiple) {
      value = [...selectedValue.value.slice(0, selectedValue.value.length - 1)]
    }

    updateValue(value)
  }
}

/**
 * Determine if an option exists within "optionList" array.
 */
function optionExists(option) {
  return optionList.value.some(_option => optionComparator(_option, option))
}

/**
 * Ensures that options are always passed as objects to scoped slots.
 */
function normalizeOptionForSlot(option) {
  return typeof option === 'object' ? option : { [props.label]: option }
}

/**
 * If push-tags is true, push the given option to `pushedTags`.
 */
function pushTag(option) {
  pushedTags.value.push(option)
}

/**
 * If there is any text in the search input, remove it.
 * Otherwise, blur the search input to close the dropdown.
 */
function onEscape() {
  if (!search.value.length) {
    inputSearchRef.value.blur()
  } else {
    search.value = ''
  }
}

/**
 * Close the dropdown on blur.
 */
function onSearchBlur(e) {
  emit('search:blur')
}

/**
 * Open the dropdown on focus.
 */
function onSearchFocus() {
  emit('search:focus')
}

/**
 * Get the text when there are not options available
 */
const noOptionsText = computed(() =>
  searching.value
    ? t('core::app.no_search_results')
    : t('core::app.not_enough_data')
)

/**
 * Determine if the component needs to track the state of values internally.
 */
const isTrackingValues = computed(
  () => typeof props.modelValue === 'undefined' || Boolean(props.reduce)
)

/**
 * The options that are currently selected.
 *
 * @return {Array}
 */
const selectedValue = computed(() => {
  let value = props.modelValue

  if (isTrackingValues.value) {
    // Vue select has to manage value internally
    value = _value.value
  }

  if (value) {
    return [].concat(value)
  }

  return []
})

/**
 * The options available to be chosen from the dropdown, including any tags that have been pushed.
 */
const optionList = computed(() =>
  props.options.concat(props.pushTags ? pushedTags.value : [])
)

/**
 * Return the current state of the search input
 */
const searching = computed(() => !!search.value)

/**
 * Return the current state of the dropdown menu.
 */
const dropdownOpen = computed(() => open.value && !mutableLoading.value)

/**
 * Return the placeholder string if it's set and there is no value selected.
 */
const searchPlaceholder = computed(() => {
  if (isValueEmpty.value && props.placeholder) {
    return props.placeholder
  }
})

/**
 * Get the total number of options in the select
 */
const totalFilteredOptions = computed(() => filteredOptions.value.length)

/**
 * The currently displayed options, filtered
 * by the search elements value. If tagging
 * true, the search text will be prepended
 * if it doesn't already exist.
 *
 * @return {Array}
 */
const filteredOptions = computed(() => {
  const list = [].concat(optionList.value)

  if (!props.filterable && !props.taggable) {
    return list
  }

  let options = search.value.length ? filter(list, search.value) : list

  if (props.taggable && search.value.length) {
    const createdOption = createOption(search.value)

    if (!optionExists(createdOption)) {
      if (props.displayNewOptionsLast) {
        options.unshift(createdOption)
      } else {
        options.push(createdOption)
      }
    }
  }

  return options
})

/**
 * Check if there aren't any options selected.
 */
const isValueEmpty = computed(() => selectedValue.value.length === 0)

/**
 * Determines if the clear button should be displayed.
 */
const showClearButton = computed(
  () => !props.multiple && props.clearable && !open.value && !isValueEmpty.value
)

if (typeof props.modelValue !== 'undefined' && isTrackingValues.value) {
  setInternalValueFromOptions(props.modelValue)
}

const { typeAheadPointer, typeAheadUp, typeAheadDown, typeAheadSelect } =
  useTypeAheadPointer(
    filteredOptions,
    toRef(props, 'selectable'),
    toRef(props, 'autoscroll'),
    dropdownMenuRef,
    select
  )

/**
 * Maybe reset the value when options change.
 * Make sure selected option is correct.
 */
watch(
  () => props.options,
  (newVal, oldVal) => {
    let shouldReset = () =>
      typeof props.resetOnOptionsChange === 'function'
        ? props.resetOnOptionsChange(newVal, oldVal, selectedValue)
        : props.resetOnOptionsChange

    if (!props.taggable && shouldReset()) {
      clearSelection()
    }

    if (props.modelValue && isTrackingValues.value) {
      setInternalValueFromOptions(props.modelValue)
    }
  }
)

/**
 * Make sure to update internal value if prop changes outside
 */
watch(
  () => props.modelValue,
  newVal => {
    if (isTrackingValues.value) {
      setInternalValueFromOptions(newVal)
    }
  }
)

/**
 * Always reset the value when the multiple prop changes.
 */
watch(() => props.multiple, clearSelection)

/**
 * Emits open/close events when the open data property changes
 */
watch(open, isOpen => {
  emit(isOpen ? 'open' : 'close')
})

/**
 * Anytime the search string changes, emit the
 * 'search' event. The event is passed with two
 * parameters: the search string, and a function
 * that accepts a boolean parameter to toggle the
 * loading state.
 */
watch(search, newVal => {
  emit('search', newVal, toggleLoading)
})

/**
 * Sync the loading prop with the internal mutable loading value.
 */
watch(
  () => props.loading,
  newVal => {
    mutableLoading.value = newVal
  }
)

let referenceElResizeObserver = null
const referenceElWidth = ref(null)

function startReferenceElResizeObserver() {
  if (
    typeof window !== 'undefined' &&
    'ResizeObserver' in window &&
    reference.value
  ) {
    referenceElResizeObserver = new ResizeObserver(([entry]) => {
      referenceElWidth.value = entry.borderBoxSize.reduce(
        (acc, { inlineSize }) => acc + inlineSize,
        0
      )
    })

    referenceElResizeObserver.observe(reference.value)
  }
}

function clearReferenceElResizeObserver() {
  if (referenceElResizeObserver) {
    referenceElResizeObserver.disconnect()
    referenceElResizeObserver = undefined
    referenceElWidth.value = null
  }
}

onMounted(() => {
  startReferenceElResizeObserver()

  onClickOutside(dropdownMenuWrapperRef, hide, {
    ignore: [reference.value],
  })

  if (reference.value.inDialog()) {
    teleportTo.value = reference.value.parentDialogIdHash()
  }
})

onBeforeUnmount(() => {
  clearReferenceElResizeObserver()
})

defineExpose({ focus, show, hide })
</script>
<style scoped>
.cs__search::-webkit-search-cancel-button {
  display: none !important;
}
.cs__search::-webkit-search-decoration,
.cs__search::-webkit-search-results-button,
.cs__search::-webkit-search-results-decoration,
.cs__search::-ms-clear {
  display: none !important;
}

.cs__search,
.cs__search:focus {
  appearance: none !important;
}
</style>
