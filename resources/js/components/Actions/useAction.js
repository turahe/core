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
import { ref, unref, shallowRef, watchEffect } from 'vue'
import { throwConfetti } from '@/utils'
import Fields from '~/Core/resources/js/components/Fields/Fields'

export function useAction(ids, options, callback) {
  const action = shallowRef(null)
  const actionIsRunning = ref(false)
  let resourceName = null

  watchEffect(() => {
    resourceName = unref(options.resourceName)
  })

  function getEndpoint() {
    return `${resourceName}/actions/${action.value.uriKey}/run`
  }

  function run() {
    if (!action.value) {
      return
    }

    if (!action.value.withoutConfirmation) {
      return showDialog()
    }

    actionIsRunning.value = true

    Innoclapps.request({
      method: 'post',
      data: {
        ids: ids.value,
        ...unref(options.requestParams),
      },
      url: getEndpoint(),
    })
      .then(({ data }) => handleResponse(data))
      .finally(() => (actionIsRunning.value = false))
  }

  function handleResponse(response) {
    if (response.openInNewTab) {
      window.open(response.openInNewTab, '_blank')
    } else {
      if (response.error) {
        Innoclapps.error(response.error)
      } else if (response.success) {
        Innoclapps.success(response.success)
      } else if (response.info) {
        Innoclapps.info(response.info)
      } else if (response.confetti) {
        throwConfetti()
      }

      let params = Object.assign({}, action.value, {
        ids: ids.value,
        response: response,
        resourceName: resourceName,
      })

      Innoclapps.$emit('action-executed', params)

      callback(params)
    }

    action.value = null
  }

  function showDialog() {
    const dialogOptions = {
      component: 'ActionDialog',
      title: action.value.name,
      message: action.value.message,
      ids: ids.value,
      endpoint: getEndpoint(),
      action: action.value,
      queryString: unref(options.requestParams),
      fields: action.value.fields ? new Fields(action.value.fields) : null,
    }

    Innoclapps.dialog()
      .confirm(dialogOptions)
      .then(dialog => handleResponse(dialog.response))
      // If canceled, set action to null because when not setting the action to null will
      // not trigger change if the user click again on the same action
      .catch(() => (action.value = null))
  }

  return { run, action, actionIsRunning, ids }
}
