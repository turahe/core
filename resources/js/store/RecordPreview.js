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
import ResourceMutations from '@/store/mutations/ResourceMutations'

const state = {
  record: {},
  viaResource: null,
  resourceName: null,
  resourceId: null,
}

const mutations = {
  ...ResourceMutations,
  /**
   * Set the preview resource data
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_PREVIEW_RESOURCE(state, data) {
    state.resourceName = data.resourceName
    state.resourceId = data.resourceId
  },

  /**
   * Set the via resource parameter
   *
   * @param {Object} state
   * @param {String|Null} resourceName
   */
  SET_VIA_RESOURCE(state, resourceName) {
    state.viaResource = resourceName
  },

  /**
   * Reset the record preview
   *
   * @param {Object} state
   */
  RESET_PREVIEW(state) {
    state.resourceName = null
    state.resourceId = null
    state.viaResource = null
    state.record = {}
  },
}

export default {
  namespaced: true,
  state,
  mutations,
}
