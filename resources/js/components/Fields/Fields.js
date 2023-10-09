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
import castArray from 'lodash/castArray'
import findIndex from 'lodash/findIndex'
import cloneDeep from 'lodash/cloneDeep'
import each from 'lodash/each'

class Fields {
  /**
   * Initialize new Fields instance
   *
   * @param  {Array|Object} fields
   *
   * @return {Void}
   */
  constructor(fields = []) {
    this.items = cloneDeep(castArray(fields))
  }

  /**
   * Set the fields to the collection
   *
   * @param {Array|Object} fields
   */
  set(fields) {
    this.items = cloneDeep(castArray(fields))

    return this
  }

  /**
   * Merge the given field attribute value with it's current form value
   * Only use if the field value is array
   *
   * @param {String} attribute
   * @param {Array} value
   */
  mergeValue(attribute, value) {
    this.updateValue(attribute, [
      ...(this.find(attribute).currentValue || []),
      ...castArray(value),
    ])

    return this
  }

  /**
   * Update the field actual initial value
   *
   * @param {String} attribute
   * @param {Mixed} value
   */
  updateValue(attribute, value) {
    this.update(attribute, {
      value,
    })
  }

  /**
   * Set fields form values
   *
   * E.q. used when the record is changed to update the fields that are already mounted
   * with the new values via the field handleChange method
   *
   * @param  {Object} data
   */
  setFormValues(data) {
    this.forEach(field =>
      field.handleChange(this.extractValueFromData(field, data))
    )

    return this
  }

  /**
   * Populate all the fields initial values from the given data
   *
   * @param  {Object} data
   */
  populate(data) {
    this.forEach(
      field => (field.value = this.extractValueFromData(field, data))
    )

    return this
  }

  /**
   * Check whether there are dirty fields in the collection
   */
  dirty() {
    const noDirty = arr => arr.every(f => f.isDirty && f.isDirty() === false)

    return noDirty(this.items) === false
  }

  /**
   * Fill field values for the given form
   *
   * @param  {Form} form
   */
  fill(form) {
    this.forEach(field => field.fill(form))

    return form
  }

  /**
   * @private
   *
   * Get the field value from the record
   *
   * @param  {Object} field
   * @param  {Object} record
   */
  extractValueFromData(field, record) {
    if (field.belongsToRelation) {
      return record[field.belongsToRelation]
    } else if (field.morphManyRelationship) {
      return record[field.morphManyRelationship]
    } else {
      // Perhaps heading field, it has no attribute
      if (field.attribute) {
        return record[field.attribute]
      }
    }
  }

  /**
   * Find single field by given attribute
   *
   * @param  {String} attribute
   */
  find(attribute) {
    let result

    this.every(field => {
      if (field.attribute == attribute) {
        result = field

        return false
      }

      return true
    })

    return result
  }

  /**
   * Determine if an field exists in the collection by attribute
   *
   * @param  {String} attribute
   */
  has(attribute) {
    return Boolean(this.find(attribute))
  }

  /**
   * Update single field by attribute
   *
   * @param  {String} attribute
   * @param  {Object} data
   */
  update(attribute, data) {
    let field = this.find(attribute)

    if (!field) {
      console.trace(
        'Cannot update field in collection as the field is not found. - ' +
          attribute
      )

      return this
    }

    each(data, (val, key) => (field[key] = val))

    return this
  }

  /**
   * Special every loop to loop through fields only
   *
   * @param  {Function} callback
   */
  every(callback) {
    this.items.every(field => {
      return callback(field)
    })

    return this
  }

  /**
   * Special foreach loop to loop through fields only
   *
   * @param  {Function} callback
   */
  forEach(callback) {
    this.items.forEach(field => callback(field))

    return this
  }

  /**
   * Push new field to collection
   *
   * @param  {Object} field
   */
  push(field) {
    this.items.push(field)

    return this
  }

  /**
   * Get fields keys/attributes
   */
  keys() {
    let result = []

    this.forEach(field => result.push(field.attribute))

    return result
  }

  /**
   * Remove field from the collection
   *
   * @param  {String} attribute
   */
  remove(attribute) {
    const index = findIndex(this.items, ['attribute', attribute])

    if (index != -1) {
      this.items.splice(index, 1)

      return true
    }

    return false
  }

  /**
   * Check whether the fields collection is not empty
   */
  isNotEmpty() {
    return this.all().length > 0
  }

  /**
   * Check whether the fields collection is empty
   */
  isEmpty() {
    return !this.isNotEmpty()
  }

  /**
   * Get all fields from the collection
   *
   * @return {Array}
   */
  all() {
    return this.items || []
  }
}

export default Fields
