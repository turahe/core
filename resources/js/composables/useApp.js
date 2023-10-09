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
import { unref, computed } from 'vue'
import get from 'lodash/get'
import { useStore } from 'vuex'

export function useApp() {
  const store = useStore()

  const locales = computed(() => store.getters.locales)

  const currentUser = computed(() => {
    return store.getters['users/current']
  })

  const users = computed(() => {
    return store.state.users.collection
  })

  function findUserById(id) {
    return store.getters['users/getById'](id)
  }

  function setting(name) {
    return get(store.state.settings, name)
  }

  function setPageTitle(title) {
    store.commit('SET_PAGE_TITLE', unref(title))
  }

  function resetStoreState() {
    store.commit('table/RESET_SETTINGS')
    store.commit('fields/RESET')
  }

  /**
   * Checks whether a Microsoft application is configured
   * The function uses the Innoclapps.config because it will check
   * whether Microsoft application credentials are configured in .env file
   * or via settings
   */
  function isMicrosoftGraphConfigured() {
    return Boolean(Innoclapps.config('microsoft.client_id'))
  }

  /**
   * Checks whether a Google project is configured
   * The function uses the Innoclapps.config because it will check
   * whether Google application credentials are configured in .env file
   * or via settings
   */
  function isGoogleApiConfigured() {
    return Boolean(Innoclapps.config('google.client_id'))
  }

  return {
    locales,
    currentUser,
    users,

    findUserById,
    setting,
    setPageTitle,
    resetStoreState,
    isGoogleApiConfigured,
    isMicrosoftGraphConfigured,
  }
}
