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
import { ref, unref, nextTick } from 'vue'
import { useResourceFields } from './useResourceFields'
import { useFieldsForm } from '~/Core/resources/js/components/Fields/useFieldsForm'

export function useResourceUpdate(resourceName) {
  const {
    fields,
    hasCollapsableFields,
    totalCollapsableFields,
    getUpdateFields,
    getDetailFields,
  } = useResourceFields()

  const { form } = useFieldsForm(fields)

  const isReady = ref(false)

  let config = {
    view: Innoclapps.config('fields.views.update'),
    id: null,

    /**
     * Provide the resouce fields callback
     *
     * @type {Promise|null}
     */
    fields: null,

    /**
     * Callback for before update record
     *
     * @type {Function|null}
     */
    onBeforeUpdate: null,

    /**
     * Callback for after update record
     *
     * @type {Function|null}
     */
    onAfterUpdate: null,

    /**
     * Callback for after fields configured
     *
     * @type {Function|null}
     */
    onFieldsConfigured: null,

    /**
     * Callback for after the update is ready
     *
     * @type {Function|null}
     */
    onReady: null,
  }

  /**
   * Update record field
   *
   * @param  {String} attribute
   * @param  {Mixed} value
   *
   * @return {Void}
   */
  function updateField(attribute, value) {
    updateFields({ [attribute]: value })
  }

  /**
   * Update record fields
   *
   * @param  {Object} data
   *
   * @return {Void}
   */
  function updateFields(data) {
    Object.keys(data).forEach(attribute =>
      fields.value.find(attribute).handleChange(data[attribute])
    )

    nextTick(update)
  }

  /**
   * Update record via store
   *
   * @return {Void}
   */
  async function update(e) {
    // Is modal, do not close the modal when a form is saved
    // as it may cause issue when the modal is route and no
    // events will be invoked as the modal will be closed
    if (e && e.target.classList.contains('modal')) {
      e.preventDefault()
    }

    if (config.onBeforeUpdate) {
      config.onBeforeUpdate(form)
    }

    let updatedRecord = await form
      .hydrate()
      .put(`/${unref(resourceName)}/${config.id}`)

    // Update fields values  as well, e.q. some fields uses the ID's
    // e.q. on MorphMany fields in this case, the field will need to ID in case of a create
    setFormValues(updatedRecord)

    Innoclapps.$emit(`${unref(resourceName)}-record-updated`, updatedRecord)

    if (config.onAfterUpdate) {
      config.onAfterUpdate(updatedRecord, form)
    }
  }

  /**
   * Get the fields for the update view
   *
   * @return {Promise}
   */
  function retrieveUpdateFields() {
    if (config.fields) {
      return config.fields()
    }

    if (config.view === Innoclapps.config('fields.views.update')) {
      return getUpdateFields(resourceName, config.id)
    } else if (config.view === Innoclapps.config('fields.views.detail')) {
      return getDetailFields(resourceName, config.id)
    }

    console.error(
      '[Invalid fields view] Unable to retrieve resource update fields.'
    )
  }

  /**
   * Init the record update
   *
   * @param  {Object} record
   *
   * @return {Void}
   */
  function init(record) {
    retrieveUpdateFields().then(updateFields => {
      setFields(updateFields, unref(record))

      nextTick(() => {
        if (!isReady.value && config.onReady) {
          config.onReady(record)
        }

        isReady.value = true
      })
    })
  }

  /**
   * Prepare the component data
   *
   * @param  {Array} updateFields
   * @param  {Object} record
   *
   * @return {Void}
   */
  function setFields(updateFields, record) {
    fields.value.set(updateFields).populate(record)

    if (config.onFieldsConfigured) {
      nextTick(config.onFieldsConfigured)
    }
  }

  /**
   * Set the fields values
   */
  function setFormValues(record) {
    fields.value.setFormValues(record)
  }

  /**
   * Boot the update for the resource
   *
   * @param  {Object} record
   *
   * @return {Void}
   */
  function boot(record, componentConfig) {
    config = Object.assign({}, config, componentConfig)

    if (!config.id) {
      config.id = record.id
    }

    init(record)
  }

  return {
    fields,
    hasCollapsableFields,
    totalCollapsableFields,
    updateField,
    updateFields,
    setFormValues,
    form,
    init,
    boot,
    update,
    isReady,
  }
}
