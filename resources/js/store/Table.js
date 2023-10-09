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
import omit from 'lodash/omit'

const state = {
  settings: {},
  customize: {},
}

const mutations = {
  /**
   * Set the table customize visibility
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_CUSTOMIZE_VISIBILTY(state, data) {
    state.customize[data.id] = data.value
  },

  /**
   * Set the table settings in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_SETTINGS(state, data) {
    state.settings[data.id] = data.settings
  },

  /**
   * Update the given table settings in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  UPDATE_SETTINGS(state, data) {
    state.settings[data.id] = Object.assign(
      {},
      state.settings[data.id],
      data.settings
    )
  },

  /**
   * Reset all tables settings
   *
   * @param {Object} state
   */
  RESET_SETTINGS(state) {
    state.settings = {}
  },
}

const actions = {
  /**
   * Fetch the given table actions
   *
   * @param  {Function} options.commit
   * @param  {Object} payload
   *
   * @return {Void}
   */
  async fetchActions({ commit }, payload) {
    let { data: settings } = await Innoclapps.request().get(
      `/${payload.resourceName}/table/settings`,
      { params: payload.params }
    )

    commit('UPDATE_SETTINGS', {
      id: payload.id,
      settings: {
        actions: settings.actions,
      },
    })

    return settings.actions
  },

  /**
   * Get the table settings
   *
   * @param  {Function} options.commit
   * @param  {Object} options.state
   * @param  {Function} options.dispatch
   * @param  {Object} payload
   *
   * @return {Object}
   */
  async getSettings({ commit, state, dispatch }, payload) {
    let currentSettings = state.settings[payload.id]
    let hasPreviousSettings = currentSettings !== undefined

    if (hasPreviousSettings && payload.force !== true) {
      return currentSettings
    }

    let { data } = await Innoclapps.request().get(
      `/${payload.resourceName}/table/settings`,
      {
        params: payload.params,
      }
    )

    commit(hasPreviousSettings ? 'UPDATE_SETTINGS' : 'SET_SETTINGS', {
      id: payload.id,
      settings: omit(data, ['filters', 'rules']),
    })

    dispatch(
      'filters/setFiltersAndRules',
      {
        identifier: data.identifier,
        filters: data.filters,
        rules: data.rules,
      },
      { root: true }
    )

    return data
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
