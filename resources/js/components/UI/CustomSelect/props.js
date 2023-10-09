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
   * The list class.
   */
  listClass: [String, Array, Object],
  /**
   * The list wrapper floating div class.
   */
  listWrapperClass: [String, Array, Object],
  /**
   * Indicates whether the option can be re-ordered
   */
  reorderable: { type: Boolean, default: false },
  /**
   * Indicates whether the select is simple.
   */
  simple: { type: Boolean, default: false },

  /**
   * The toggle icon.
   */
  toggleIcon: { type: String, default: 'Selector' },

  /**
   * Toggles the adding of a 'loading' class to the main
   * .v-select wrapper. Useful to control UI state when
   * results are being processed through AJAX.
   */
  loading: { type: Boolean, default: false },

  /**
   * Sets the id of the input element.
   */
  inputId: String,

  /**
   * Set the tabindex for the input field.
   */
  tabindex: Number,

  /**
   * Value of the 'autocomplete' field of the input element.
   */
  autocomplete: { type: String, default: 'off' },

  /**
   * Equivalent to the `placeholder` attribute on an `<input>`.
   */
  placeholder: { type: String, default: '' },

  autoscroll: { type: Boolean, default: true },

  /**
   * Indicates whether the select is bordered
   */
  bordered: { type: Boolean, default: true },

  /**
   * Indicates whether the select is with shadow
   */
  shadow: { type: Boolean, default: true },

  /**
   * Indicates whether the select is rounded
   */
  rounded: { type: Boolean, default: true },

  /**
   * Select size
   */
  size: {
    type: [String, Boolean],
    default: 'md',
    validator(value) {
      return ['sm', 'lg', 'md', '', false].includes(value)
    },
  },

  /**
   * Contains the currently selected value. Very similar to a
   * `value` attribute on an <input>. You can listen for changes
   * using 'change' event using v-on
   * @type {Object||String||null}
   */
  modelValue: {},

  /**
   * An array of strings or objects to be used as dropdown choices.
   * If you are using an array of objects, vue-select will look for
   * a `label` key (ex. [{label: 'This is Foo', value: 'foo'}]). A
   * custom label key can be set with the `label` prop.
   */
  options: {
    type: Array,
    default() {
      return []
    },
  },

  /**
   * Disable the entire component.
   */
  disabled: { type: Boolean, default: false },

  /**
   * Can the user clear the selected property.
   */
  clearable: { type: Boolean, default: true },

  /**
   * Enable/disable filtering the options.
   */
  searchable: { type: Boolean, default: true },

  /**
   * Equivalent to the `multiple` attribute on a `<select>` input.
   */
  multiple: { type: Boolean, default: false },

  /**
   * Enables/disables clearing the search text when an option is selected.
   */
  clearSearchOnSelect: { type: Boolean, default: true },

  /**
   * Close a dropdown when an option is chosen. Set to false to keep the dropdown
   * open (useful when combined with multi-select, for example)
   */
  closeOnSelect: { type: Boolean, default: true },

  /**
   * Tells vue-select what key to use when generating option
   * labels when each `option` is an object.
   */
  label: { type: String, default: 'label' },

  /**
   * Callback to generate the label text. If {option}
   * is an object, returns option[props.label] by default.
   *
   * Label text is used for filtering comparison and
   * displaying. If you only need to adjust the
   * display, you should use the `option` and
   * `selected-option` slots.
   *
   * @type {Function}
   *
   * @param  {Object || String} option
   *
   * @return {String}
   */
  optionLabelProvider: Function,

  /**
   * When working with objects, the reduce
   * prop allows you to transform a given
   * object to only the information you
   * want passed to a v-model binding
   * or @input event.
   */
  reduce: { type: Function, default: option => option },

  /**
   * Decides whether an option is selectable or not. Not selectable options
   * are displayed but disabled and cannot be selected.
   *
   * @type {Function}
   *
   * @param {Object|String} option
   *
   * @return {Boolean}
   */
  selectable: { type: Function, default: option => true },

  /**
   * Enable/disable creating options from searchEl.
   */
  taggable: { type: Boolean, default: false },

  /**
   * When true, newly created tags will be added to
   * the options list.
   */
  pushTags: { type: Boolean, default: false },

  /**
   * When true, existing options will be filtered
   * by the search text. Should not be used in conjunction
   * with taggable.
   */
  filterable: { type: Boolean, default: true },

  /**
   * Callback to determine if the provided option should
   * match the current search text. Used to determine
   * if the option should be displayed.
   *
   * @type   {Function}
   *
   * @param  {Object|String} option
   * @param  {String} label
   * @param  {String} search
   *
   * @return {Boolean}
   */
  filterBy: {
    type: Function,
    default(option, label, search) {
      return (label || '').toLowerCase().indexOf(search.toLowerCase()) > -1
    },
  },

  /**
   * User defined function for adding options
   */
  createOptionProvider: Function,

  /**
   * User defined function for getting the option key
   */
  optionKeyProvider: Function,

  optionComparatorProvider: Function,

  displayNewOptionsLast: { type: Boolean, default: false },

  /**
   * When false, updating the options will not reset the selected value. Accepts
   * a `boolean` or `function` that returns a `boolean`. If defined as a function,
   * it will receive the params listed below.
   *
   * @type {Boolean|Function}
   *
   * @param {Array} newOptions
   * @param {Array} oldOptions
   * @param {Array} selectedValue
   */
  resetOnOptionsChange: {
    default: false,
    validator: value => ['function', 'boolean'].includes(typeof value),
  },
}
