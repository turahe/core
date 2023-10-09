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
  <IFormInput
    ref="inputRef"
    :placeholder="placeholder"
    :disabled="disabled"
    @blur="onBlurHandler"
    @input="onInputHandler"
    @focus="onFocusHandler"
    v-model="amount"
    @change="onChangeHandler"
    v-bind="$attrs"
    type="tel"
  />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { unformat, toFixed, formatMoney } from 'accounting-js'
import numericInputProps from './numericInputProps'
const emit = defineEmits(['change', 'blur', 'focus', 'update:modelValue'])
const props = defineProps(numericInputProps)

const amount = ref('')
const inputRef = ref(null)
let timeoutClear = null

/**
 * Number type of formatted value.
 * @return {Number}
 */
const amountNumber = computed(() => unformatValue(amount.value))

/**
 * Number type of value props.
 * @return {Number}
 */
const valueNumber = computed(() => unformatValue(props.modelValue, '.'))

/**
 * Define decimal separator based on separator props.
 * @return {String} '.' or ','
 */
const decimalSeparatorSymbol = computed(() => {
  if (typeof props.decimalSeparator !== 'undefined')
    return props.decimalSeparator
  if (props.separator === ',') return '.'
  return ','
})

/**
 * Define thousand separator based on separator props.
 * @return {String} '.' or ','
 */
const thousandSeparatorSymbol = computed(() => {
  if (typeof props.thousandSeparator !== 'undefined')
    return props.thousandSeparator
  if (props.separator === '.') return '.'
  if (props.separator === 'space') return ' '
  return ','
})

/**
 * Define format position for currency symbol and value.
 * @return {String} format
 */
const symbolPosition = computed(() => {
  if (!props.currency) return '%v'
  return props.currencySymbolPosition === 'suffix' ? '%v %s' : '%s %v'
})

/**
 * Watch for value change from other input with same v-model.
 * @param {Number} newValue
 */
watch(valueNumber, newVal => {
  if (inputRef.value.inputRef !== document.activeElement) {
    amount.value = format(newVal)
  }
})

/**
 * Immediately reflect props changes
 */
watch(
  [() => props.separator, () => props.currency, () => props.precision],
  () => {
    process(valueNumber.value)
    amount.value = format(valueNumber.value)
  }
)

onMounted(() => {
  // Set default value props when valueNumber has some value
  if (valueNumber.value || isDeliberatelyZero()) {
    process(valueNumber.value)
    amount.value = format(valueNumber.value)

    // In case of delayed props value.
    timeoutClear = setTimeout(() => {
      process(valueNumber.value)
      amount.value = format(valueNumber.value)
    }, 500)
  }
})
onBeforeUnmount(() => {
  timeoutClear && clearTimeout(timeoutClear)
})

/**
 * Handle change event.
 * @param {Object} e
 */
function onChangeHandler(e) {
  emit('change', e)
}

/**
 * Handle blur event.
 * @param {Object} e
 */
function onBlurHandler(e) {
  emit('blur', e)
  amount.value = format(valueNumber.value)
}

/**
 * Handle focus event.
 * @param {Object} e
 */
function onFocusHandler(e) {
  emit('focus', e)
  if (valueNumber.value === 0) {
    amount.value = null
  } else {
    amount.value = formatMoney(valueNumber.value, {
      symbol: '',
      format: '%v',
      thousand: '',
      decimal: decimalSeparatorSymbol.value,
      precision: Number(props.precision),
    })
  }
}

/**
 * Handle input event.
 */
function onInputHandler(e) {
  process(amountNumber.value)
}

/**
 * Validate value before update the component.
 * @param {Number} value
 */
function process(value) {
  if (value >= props.max) update(props.max)
  if (value <= props.min) update(props.min)
  if (value > props.min && value < props.max) update(value)
  if (!props.minus && value < 0) props.min >= 0 ? update(props.min) : update(0)
}

/**
 * Update parent component model value.
 * @param {Number} value
 */
function update(value) {
  const fixedValue = toFixed(value, props.precision)
  const output =
    props.outputType.toLowerCase() === 'string'
      ? fixedValue
      : Number(fixedValue)
  emit('update:modelValue', output)
}

/**
 * Format value using symbol and separator.
 * @param {Number} value
 * @return {String}
 */
function format(value) {
  return formatMoney(value, {
    symbol: props.currency,
    format: symbolPosition.value,
    precision: Number(props.precision),
    decimal: decimalSeparatorSymbol.value,
    thousand: thousandSeparatorSymbol.value,
  })
}

/**
 * Remove symbol and separator.
 * @param {Number} value
 * @param {String} decimalSeparator
 * @return {Number}
 */
function unformatValue(value, decimalSeparator) {
  const toUnformat =
    typeof value === 'string' && value === '' ? props.emptyValue : value

  return unformat(toUnformat, decimalSeparator || decimalSeparatorSymbol.value)
}

/**
 * Check if value was deliberately set to zero and not just evaluated
 * @return {boolean}
 */
function isDeliberatelyZero() {
  return valueNumber.value === 0 && props.modelValue !== ''
}
</script>
