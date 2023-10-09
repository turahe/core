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
import Errors from './Errors'
import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import { isFile, objectToFormData } from './utils'

class Form {
  /**
   * Create a new form instance.
   *
   * @param {Object} data
   * @param {Object} options
   */
  constructor(data = {}, options = {}) {
    this.busy = false
    this.recentlySuccessful = false
    this.errors = new Errors()
    this.queryString = {}
    this.withData(data).withOptions(options)
  }

  /**
   * Add form data
   *
   * @param {Object} data
   */
  withData(data) {
    this.successful = false

    this.setInitialValues(data)

    for (const field in data) {
      this[field] = data[field]
    }

    return this
  }

  /**
   * Set the form initial data
   *
   * @param {Object} values
   */
  setInitialValues(values) {
    this.originalData = cloneDeep(values)
  }

  /**
   * Add form options
   */
  withOptions(options) {
    this.__options = {
      resetOnSuccess: false,
    }

    if (options.hasOwnProperty('resetOnSuccess')) {
      this.__options.resetOnSuccess = options.resetOnSuccess
    }

    return this
  }

  /**
   * Populate form data.
   *
   * @param {Object} data
   */
  populate(data) {
    this.keys().forEach(key => {
      this[key] = data[key]
    })

    return this
  }

  /**
   * Set initial form data/attribute.
   * E.q. can be used when resetting the form
   *
   * @param {String|Object} attribute
   * @param {Mixed} value
   */
  set(attribute, value = null) {
    if (typeof attribute === 'object') {
      Object.keys(attribute).forEach(key => this.set(key, attribute[key]))
    } else {
      this.fill(attribute, value)
      this.originalData[attribute] = cloneDeep(value)
    }

    return this
  }

  /**
   * Fill form data/attribute.
   *
   * @param {String|Object} attribute
   * @param {Mixed} value
   */
  fill(attribute, value = null) {
    if (typeof attribute === 'object') {
      Object.keys(attribute).forEach(key => this.fill(key, attribute[key]))
    } else {
      this[attribute] = value
    }

    return this
  }

  /**
   * Add form query string
   *
   * @param {Object} values
   */
  withQueryString(values) {
    this.queryString = { ...this.queryString, ...values }

    return this
  }

  /**
   * Get the form data.
   *
   * @return {Object}
   */
  data() {
    return this.keys().reduce(
      (data, key) => ({ ...data, [key]: this[key] }),
      {}
    )
  }

  /**
   * Get the form data keys.
   *
   * @return {Array}
   */
  keys() {
    return Object.keys(this).filter(key => !this.ignore().includes(key))
  }

  /**
   * Start processing the form.
   */
  startProcessing() {
    this.errors.clear()
    this.busy = true
    this.successful = false

    return this
  }

  /**
   * Finish processing the form.
   */
  finishProcessing() {
    this.busy = false
    this.successful = true
    this.recentlySuccessful = true

    if (this.__options.resetOnSuccess) {
      this.reset()
    }

    setTimeout(() => (this.recentlySuccessful = false), 3000)

    return this
  }

  /**
   * Clear the form data and it's errors
   */
  clear() {
    this.keys().forEach(key => {
      delete this[key]
    })

    this.successful = false

    this.errors.clear()

    this.queryString = {}
    this.setInitialValues({})

    return this
  }

  /**
   * Reset the form data.
   */
  reset() {
    this.keys().forEach(key => {
      this[key] = cloneDeep(this.originalData[key])
    })

    return this
  }

  /**
   * Get the first error message for the given field.
   *
   * @param {String} field
   * @return {String}
   */
  getError(field) {
    return this.errors.first(field)
  }

  /**
   * Submit the form via a GET request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  get(url, config = {}) {
    return this.submit('get', url, config)
  }

  /**
   * Submit the form via a POST request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  post(url, config = {}) {
    return this.submit('post', url, config)
  }

  /**
   * Submit the form via a PATCH request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  patch(url, config = {}) {
    return this.submit('patch', url, config)
  }

  /**
   * Submit the form via a PUT request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  put(url, config = {}) {
    return this.submit('put', url, config)
  }

  /**
   * Submit the form via a DELETE request.
   *
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  delete(url, config = {}) {
    return this.submit('delete', url, config)
  }

  /**
   * Submit the form data via an HTTP request.
   *
   * @param  {String} method (get, post, patch, put)
   * @param  {String} url
   * @param  {Object} config (axios config)
   * @return {Promise}
   */
  submit(method, url, config = {}) {
    this.startProcessing()

    let urlData = this.createUriData(url)
    const data =
      method === 'get'
        ? {
            params: merge(urlData.queryString, this.data()),
          }
        : this.hasFiles()
        ? objectToFormData(this.data())
        : this.data()

    return new Promise((resolve, reject) => {
      Innoclapps.request()
        [method](
          urlData.uri,
          data,
          merge(
            {
              params: urlData.queryString,
            },
            config
          )
        )
        .then(response => {
          this.finishProcessing()

          resolve(response.data)
        })
        .catch(error => {
          this.busy = false
          if (error.response) {
            this.errors.set(this.extractErrors(error.response))
          }
          reject(error)
        })
    })
  }

  /**
   * Extract the errors from the response object.
   *
   * @param  {Object} response
   * @return {Object}
   */
  extractErrors(response) {
    if (!response.data || typeof response.data !== 'object') {
      return { error: Form.errorMessage }
    }

    if (response.data.errors) {
      return { ...response.data.errors }
    }

    if (response.data.message) {
      return { error: response.data.message }
    }

    return { ...response.data }
  }

  /**
   * Get a named route.
   *
   * @param  {String} url
   *
   * @return {Object}
   */
  createUriData(url) {
    let urlArray = url.split('?')
    let params = urlArray[1]
      ? Object.fromEntries(new URLSearchParams(urlArray[1]))
      : {}

    return {
      uri: urlArray[0],
      queryString: merge(params, this.queryString),
    }
  }

  /**
   * Clear errors on keydown.
   *
   * @param {KeyboardEvent} event
   */
  onKeydown(event) {
    if (this.errors.has(event)) {
      this.errors.clear(event)
      return
    }

    if (event.target.name) {
      this.errors.clear(event.target.name)
    } else if (event.target.id) {
      this.errors.clear(event.target.id)
    }
  }

  hasFiles() {
    for (const property in this.originalData) {
      if (this.hasFilesDeep(this[property])) {
        return true
      }
    }

    return false
  }

  hasFilesDeep(object) {
    if (object === null) {
      return false
    }

    if (typeof object === 'object') {
      for (const key in object) {
        if (object.hasOwnProperty(key)) {
          if (this.hasFilesDeep(object[key])) {
            return true
          }
        }
      }
    }

    if (Array.isArray(object)) {
      for (const key in object) {
        if (object.hasOwnProperty(key)) {
          return this.hasFilesDeep(object[key])
        }
      }
    }

    return isFile(object)
  }

  /**
   * The attributes that should be ignored as data
   */
  ignore() {
    return [
      '__options',
      'busy',
      'successful',
      'recentlySuccessful',
      'errors',
      'originalData',
      'queryString',
    ]
  }
}

Form.errorMessage = 'Something went wrong. Please try again.'

export default Form
