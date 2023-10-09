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
export default {
  /**
   * Currency symbol.
   */
  currency: {
    type: String,
    default: '',
    required: false,
  },

  /**
   * Maximum value allowed.
   */
  max: {
    type: Number,
    default: Number.MAX_SAFE_INTEGER || 9007199254740991,
    required: false,
  },

  /**
   * Minimum value allowed.
   */
  min: {
    type: Number,
    default: Number.MIN_SAFE_INTEGER || -9007199254740991,
    required: false,
  },

  /**
   * Enable/Disable minus value.
   */
  minus: {
    type: Boolean,
    default: false,
    required: false,
  },

  /**
   * Input placeholder.
   */
  placeholder: {
    type: String,
    default: '',
    required: false,
  },

  /**
   * Value when the input is empty
   */
  emptyValue: {
    type: [Number, String],
    default: '',
    required: false,
  },

  /**
   * Number of decimals.
   * Decimals symbol are the opposite of separator symbol.
   */
  precision: {
    type: Number,
    default() {
      return Number(Innoclapps.config('currency.precision'))
    },
    required: false,
  },

  /**
   * Thousand separator type.
   * Separator props accept either . or , (default).
   */
  separator: {
    type: String,
    default: ',',
    required: false,
  },

  /**
   * Forced thousand separator.
   * Accepts any string.
   */
  thousandSeparator: {
    default() {
      return Innoclapps.config('currency.thousands_separator')
    },
    required: false,
    type: String,
  },

  /**
   * Forced decimal separator.
   * Accepts any string.
   */
  decimalSeparator: {
    default() {
      return Innoclapps.config('currency.decimal_mark')
    },
    required: false,
    type: String,
  },
  /**
   * The output type used for v-model.
   * It can either be String or Number (default).
   */
  outputType: {
    required: false,
    type: String,
    default: 'Number',
  },

  /**
   * v-model value.
   */
  modelValue: {
    type: [Number, String],
    default: '',
    required: true,
  },

  disabled: {
    type: Boolean,
    default: false,
    required: false,
  },

  /**
   * Position of currency symbol
   * Symbol position props accept either 'suffix' or 'prefix' (default).
   */
  currencySymbolPosition: {
    type: String,
    default: 'prefix',
    required: false,
  },
}
