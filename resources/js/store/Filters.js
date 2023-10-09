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
import findIndex from 'lodash/findIndex'
import sortBy from 'lodash/sortBy'
import find from 'lodash/find'
import { getDefaultQuery } from '~/Core/resources/js/components/QueryBuilder/utils'

const state = {
  // The available saved filters
  filters: {},
  // The current available rules
  rules: {},
  // The v-model rules in the query builder
  queryBuilderRules: {},
  // The resources where the rules are visible
  visibleRules: {},
  // Valid rules indicator
  rulesAreValidFor: {},
  // Indicates which resources has rules applied
  hasRulesApplied: {},
  // Active filters
  activeFilters: {},
}

const mutations = {
  /**
   * Set the saved filters in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET(state, data) {
    state.filters[data.identifier] = data.filters
  },

  /**
   * Set active rules in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_RULES(state, data) {
    state.rules[data.identifier] = data.rules
  },

  /**
   * Update the filter in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  UPDATE(state, data) {
    let index = findIndex(state.filters[data.identifier], [
      'id',
      Number(data.filter.id),
    ])

    if (index !== -1) {
      state.filters[data.identifier][index] = data.filter
    }
  },

  /**
   * Add new saved filter in store
   *
   * @param {Object} state
   * @param {Object} data
   */
  PUSH(state, data) {
    state.filters[data.identifier].push(data.filter)
  },

  /**
   * Remove filter from store
   *
   * @param {Object} state
   * @param {Object} data
   */
  REMOVE(state, data) {
    let index = findIndex(state.filters[data.identifier], [
      'id',
      Number(data.id),
    ])

    if (index !== -1) {
      state.filters[data.identifier].splice(index, 1)
    }
  },

  /**
   * Set filter as active
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_ACTIVE(state, data) {
    if (!state.activeFilters[data.identifier]) {
      state.activeFilters[data.identifier] = {}
    }

    state.activeFilters[data.identifier][data.view] = data.id
  },

  /**
   * Clear active filter
   *
   * @param {Object} state
   * @param {Object} data
   */
  CLEAR_ACTIVE(state, data) {
    delete state.activeFilters[data.identifier][data.view]
  },

  /**
   * Unmark the given filter as default
   *
   * @param {Object} state
   * @param {Object} data
   */
  UNMARK_AS_DEFAULT(state, data) {
    let index = findIndex(state.filters[data.identifier], [
      'id',
      Number(data.id),
    ])

    if (index !== -1) {
      let defaultViewIndex = findIndex(
        state.filters[data.identifier][index].defaults,
        { view: data.view, user_id: data.userId }
      )

      if (defaultViewIndex !== -1) {
        state.filters[data.identifier][index].defaults.splice(
          defaultViewIndex,
          1
        )
      }
    }
  },

  /**
   * Set rules are visible indicator for resource
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_RULES_VISIBLE(state, data) {
    if (!state.visibleRules[data.identifier]) {
      state.visibleRules[data.identifier] = {}
    }
    state.visibleRules[data.identifier][data.view] = data.visible
  },

  /**
   * Set rules are valid indicator for resource
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_RULES_ARE_VALID(state, data) {
    if (!state.rulesAreValidFor[data.identifier]) {
      state.rulesAreValidFor[data.identifier] = {}
    }
    state.rulesAreValidFor[data.identifier][data.view] = data.value
  },

  /**
   * Set has rules applied indicator for resource
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_HAS_RULES_APPLIED(state, data) {
    if (!state.hasRulesApplied[data.identifier]) {
      state.hasRulesApplied[data.identifier] = {}
    }
    state.hasRulesApplied[data.identifier][data.view] = data.value
  },

  /**
   * Reset the query builder rules for the resource
   *
   * @param {Object} state
   * @param {Object} data
   */
  RESET_BUILDER_RULES(state, data) {
    if (!state.queryBuilderRules[data.identifier]) {
      state.queryBuilderRules[data.identifier] = {}
    }
    state.queryBuilderRules[data.identifier][data.view] = getDefaultQuery()
  },

  /**
   * Set the query builder rules for the resource
   *
   * @param {Object} state
   * @param {Object} data
   */
  SET_BUILDER_RULES(state, data) {
    if (!state.queryBuilderRules[data.identifier]) {
      state.queryBuilderRules[data.identifier] = {}
    }
    state.queryBuilderRules[data.identifier][data.view] = data.rules
  },

  /**
   * Add group to the query
   */
  ADD_QUERY_GROUP(state, query) {
    query.children.push({
      type: 'group',
      query: getDefaultQuery(),
    })
  },

  /**
   * Set the query children
   */
  SET_QUERY_CHILDREN(state, data) {
    data.query.children = data.children
  },

  /**
   * Add query children
   */
  ADD_QUERY_CHILD(state, data) {
    data.query.children.push(data.child)
  },

  /**
   * Remove child from the query
   */
  REMOVE_QUERY_CHILD(state, data) {
    data.query.children.splice(data.index, 1)
  },

  /**
   * Update query value
   */
  UPDATE_QUERY_VALUE(state, data) {
    data.query.value = data.value
  },

  /**
   * Update query condition
   */
  UPDATE_QUERY_CONDITION(state, data) {
    data.query.condition = data.value
  },

  /**
   * Update query operand
   */
  UPDATE_QUERY_OPERAND(state, data) {
    data.query.operand = data.value
  },

  /**
   * Update query operator
   */
  UPDATE_QUERY_OPERATOR(state, data) {
    data.query.operator = data.value
  },
}

const findNested = (rules, ruleId) => {
  let found = null
  rules.every(rule => {
    if (rule.query.hasOwnProperty('children') && rule.query.children) {
      found = findNested(rule.query.children, ruleId)
    } else if (rule.query.rule == ruleId) {
      found = rule
    }
    return found ? false : true
  })

  return found
}

const getters = {
  findRuleInQueryBuilder: state => (identifier, view, rule) => {
    if (
      !state.queryBuilderRules[identifier] ||
      !state.queryBuilderRules[identifier][view] ||
      !state.queryBuilderRules[identifier][view].children
    ) {
      return
    }

    return findNested(state.queryBuilderRules[identifier][view].children, rule)
  },
  /**
   * Get all resource saved filters
   *
   * @param  {Object} state
   * @param  {String} identifier
   *
   * @return {Array}
   */
  getAll: state => identifier => {
    return sortBy(
      state.filters[identifier],
      ['is_system_default', 'name'],
      'desc',
      'asc'
    )
  },

  /**
   * Get resource saved filter by id
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {Number} id
   *
   * @return {Object}
   */
  getById: state => (identifier, id) => {
    return find(state.filters[identifier], ['id', id])
  },

  /**
   * Get resource default filter
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   * @param  {Number} userId
   *
   * @return {Object|null}
   */
  getDefault: state => (identifier, view, userId) => {
    return find(state.filters[identifier], filter => {
      return find(filter.defaults, { view: view, user_id: userId })
    })
  },

  /**
   * Get resource active filter
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   *
   * @return {Object|null}
   */
  getActive: state => (identifier, view) => {
    if (
      !state.activeFilters[identifier] ||
      !state.activeFilters[identifier][view]
    ) {
      return null
    }

    return find(state.filters[identifier], [
      'id',
      Number(state.activeFilters[identifier][view]),
    ])
  },

  /**
   * Get the resource query builder rules
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   *
   * @return {Object}
   */
  getBuilderRules: state => (identifier, view) => {
    if (
      !state.queryBuilderRules[identifier] ||
      !state.queryBuilderRules[identifier][view]
    ) {
      return {}
    }

    return state.queryBuilderRules[identifier][view] || {}
  },

  /**
   * Check whether the rules are visible for the given identifier and view
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   *
   * @return {Boolean}
   */
  rulesAreVisible: state => (identifier, view) => {
    if (
      !state.visibleRules[identifier] ||
      !state.visibleRules[identifier][view]
    ) {
      return false
    }

    return state.visibleRules[identifier][view] || false
  },

  /**
   * Check whether rules are applied for the given identifier and view
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   *
   * @return {Boolean}
   */
  hasRulesApplied: state => (identifier, view) => {
    if (
      !state.hasRulesApplied[identifier] ||
      !state.hasRulesApplied[identifier][view]
    ) {
      return false
    }

    return state.hasRulesApplied[identifier][view] || false
  },

  /**
   * Check whether rules are valid for the given identifier and view
   *
   * @param  {Object} state
   * @param  {String} identifier
   * @param  {String} view
   *
   * @return {Boolean}
   */
  rulesAreValid: state => (identifier, view) => {
    if (
      !state.rulesAreValidFor[identifier] ||
      !state.rulesAreValidFor[identifier][view]
    ) {
      return false
    }

    return state.rulesAreValidFor[identifier][view] || false
  },

  /**
   * Check whether the given resource has rules defined
   *
   * @param  {Object} state
   * @param  {String} identifier
   *
   * @return {Boolean}
   */
  hasRules: state => identifier => {
    if (!state.rules[identifier]) {
      return false
    }

    return state.rules[identifier].length > 0
  },
}

const actions = {
  /**
   * Set the available saved filters and the available rules
   *
   * @param {Function} options.commit
   * @param {Object} data
   */
  setFiltersAndRules({ commit }, data) {
    commit('SET', {
      identifier: data.identifier,
      filters: data.filters,
    })

    commit('SET_RULES', {
      identifier: data.identifier,
      rules: data.rules,
    })
  },

  /**
   * Clear active filter
   *
   * @param  {Function} options.commit
   * @param  {Object} options.getters
   * @param  {Object} data
   *
   * @return {Boolean}
   */
  clearActive({ commit, getters }, data) {
    let filter = getters.getActive(data.identifier, data.view)

    if (filter) {
      commit('CLEAR_ACTIVE', {
        identifier: data.identifier,
        view: data.view,
      })

      commit('RESET_BUILDER_RULES', {
        identifier: data.identifier,
        view: data.view,
      })

      return true
    }

    return false
  },

  /**
   * Delete saved filter
   *
   * @param  {Function} options.commit
   * @param  {Object} options.getters
   * @param  {Object} payload
   *
   * @return {Void}
   */
  async destroy({ commit, getters }, payload) {
    await Innoclapps.request().delete(`filters/${payload.id}`)

    let active = getters.getActive(payload.identifier, payload.view)

    if (active && Number(payload.id) === Number(active.id)) {
      commit('CLEAR_ACTIVE', {
        identifier: payload.identifier,
        view: payload.view,
      })
    }

    commit('REMOVE', {
      identifier: payload.identifier,
      id: payload.id,
    })
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
}
