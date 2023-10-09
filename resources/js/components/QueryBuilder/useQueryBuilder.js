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

export function useQueryBuilder(identifier, view) {
  const store = useStore()

  const viewName = unref(view || identifier)

  /**
   * Get the currently rules in the builder
   */
  const queryBuilderRules = computed({
    set(newValue) {
      store.commit('filters/SET_BUILDER_RULES', {
        identifier: unref(identifier),
        view: viewName,
        rules: newValue,
      })
    },
    get() {
      return (
        store.getters['filters/getBuilderRules'](unref(identifier), viewName) ||
        {}
      )
    },
  })

  /**
   * Indicates wheter there are rules in the builder
   */
  const hasBuilderRules = computed(
    () =>
      queryBuilderRules.value.children &&
      queryBuilderRules.value.children.length > 0
  )

  /**
   * Indicates whether the rules in the builder are valid
   */
  const rulesAreValid = computed({
    set(newValue) {
      store.commit('filters/SET_RULES_ARE_VALID', {
        identifier: unref(identifier),
        view: viewName,
        value: newValue,
      })
    },
    get() {
      return store.getters['filters/rulesAreValid'](unref(identifier), viewName)
    },
  })

  /**
   * Indicates whether there are rules applied in the query builder
   */
  const hasRulesApplied = computed({
    set(newValue) {
      store.commit('filters/SET_HAS_RULES_APPLIED', {
        identifier: unref(identifier),
        view: viewName,
        value: newValue,
      })
    },
    get() {
      return store.getters['filters/hasRulesApplied'](
        unref(identifier),
        viewName
      )
    },
  })

  /**
   * Indicates whether the filters rules are visible
   */
  const rulesAreVisible = computed({
    set(newValue) {
      store.commit('filters/SET_RULES_VISIBLE', {
        identifier: unref(identifier),
        view: viewName,
        visible: newValue,
      })
    },
    get() {
      return store.getters['filters/rulesAreVisible'](
        unref(identifier),
        viewName
      )
    },
  })

  /**
   * Toggle the query builder visibility
   */
  function toggleFiltersRules() {
    rulesAreVisible.value = !rulesAreVisible.value
  }

  /**
   * Find rule from the query builder from the given rule attribute ID
   */
  function findRule(ruleId) {
    return store.getters['filters/findRuleInQueryBuilder'](
      unref(identifier),
      viewName,
      ruleId
    )
  }

  /**
   * Reset the query builder rules
   */
  function resetQueryBuilderRules() {
    store.commit('filters/RESET_BUILDER_RULES', {
      identifier: unref(identifier),
      view: viewName,
    })
  }

  return {
    queryBuilderRules,
    rulesAreVisible,
    hasBuilderRules,
    hasRulesApplied,
    rulesAreValid,
    toggleFiltersRules,
    findRule,
    resetQueryBuilderRules,
  }
}
