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
import { computed } from 'vue'
import Form from '~/Core/resources/js/services/Form/Form'
import Fields from '~/Core/resources/js/components/Fields/Fields'
import cloneDeep from 'lodash/cloneDeep'
import { randomString } from '@/utils'

class FieldsForm extends Form {
  /**
   * @param {Fields} fields
   * @param {Object} data
   * @param {options} data
   * @param {null|String} formId
   */
  constructor(fields, data = {}, options = {}, formId = null) {
    super(data, options)

    // fields are passed as ref, but Vue JS make them reactive
    // but references to the original ref so both are reactive
    this.fields = fields
    this.formId = formId || randomString()
  }

  /**
   * Indicates whether the form is dirty
   */
  get isDirty() {
    return computed(() => this.fields instanceof Fields && this.fields.dirty())
  }

  /**
   * Reset the form data.
   */
  reset() {
    // Reset any additional fields
    super.reset()

    const keys = this.keys()

    this.fields.forEach(field => {
      field.handleChange(
        cloneDeep(this.originalData[keys[keys.indexOf(field.attribute)]])
      )
    })
  }

  /**
   * Hydrate the form data from the fields
   */
  hydrate() {
    this.fields.fill(this)

    return this
  }

  /**
   * The attributes that should be ignored as data
   */
  ignore() {
    let ignored = super.ignore()

    ignored.push('fields')
    ignored.push('formId')

    return ignored
  }
}

export default FieldsForm
