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
import { useStore } from 'vuex'
import { useApp } from '~/Core/resources/js/composables/useApp'

export function useFilterable(identifier, view) {
  const store = useStore()
  const { currentUser } = useApp()

  const viewName = unref(view || identifier)

  /**
   * Get the currently query builder active filter
   */
  const activeFilter = computed({
    set(newValue) {
      if (newValue === null) {
        store.dispatch('filters/clearActive', {
          identifier: unref(identifier),
          view: viewName,
        })
      } else {
        store.commit('filters/SET_ACTIVE', {
          identifier: unref(identifier),
          view: viewName,
          id: newValue,
        })
      }
    },
    get() {
      return store.getters['filters/getActive'](unref(identifier), viewName)
    },
  })

  /**
   * Indicates whether the resource has available rules/filters
   */
  const hasRules = computed(() =>
    store.getters['filters/hasRules'](unref(identifier))
  )

  /**
   * Get the identifier available saved filters ordered by name
   */
  const filters = computed({
    set(newValue) {
      store.commit('filters/SET', {
        identifier: unref(identifier),
        filters: newValue,
      })
    },
    get() {
      return store.getters['filters/getAll'](unref(identifier))
    },
  })

  /**
   * Get the identifier available rules
   */
  const rules = computed({
    set(newValue) {
      store.commit('filters/SET_RULES', {
        identifier: unref(identifier),
        rules: newValue,
      })
    },
    get() {
      return store.state.filters.rules[unref(identifier)]
    },
  })

  /**
   * Current user default filter
   */
  const userDefaultFilter = computed(() => getDefault(currentUser.value.id))

  /**
   * Get the default filter for the given user
   */
  function getDefault(userId) {
    return store.getters['filters/getDefault'](
      unref(identifier),
      viewName,
      userId
    )
  }

  /**
   * Find filter by given ID
   */
  function findFilter(id) {
    return store.getters['filters/getById'](unref(identifier), id)
  }

  return {
    filters,
    rules,
    activeFilter,
    hasRules,
    userDefaultFilter,
    findFilter,
  }
}
