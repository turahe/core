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
import { ref, computed, nextTick } from 'vue'
import { useGate } from './useGate'
import { useRecordStore } from './useRecordStore'
import findIndex from 'lodash/findIndex'

export function useRecordTab(config) {
  const page = ref(1)
  const search = ref(null)
  const searchResults = ref(null)
  const dataLoadedFirstTime = ref(false)
  const defaultPerPage = ref(config.perPage || 15)
  const { gate } = useGate()

  const {
    record,
    addResourceRecordHasManyRelationship,
    updateResourceRecordHasManyRelationship,
    resetRecordHasManyRelationship,
  } = useRecordStore()

  const hasSearchResults = computed(
    () => searchResults.value && searchResults.value.length > 0
  )

  const isPerformingSearch = computed(() => search.value !== null)

  /**
   * Perform search
   *
   * @param  {String|null} value
   * @param  {string} associateable
   *
   * @return {Void}
   */
  function performSearch(value, associateable) {
    // Reset the state in case complete so the infinity
    // loading can be performed again
    config.infinityRef.value.state.reset()

    // Reset the page as for each search, the page must be
    // resetted to start from zero, additional pages results
    // are again handle by infinity loader when user scrolling to bottom
    // This also helps when user remove the search value so the infinity
    // loader can load the actual data from page 1 again
    page.value = 1

    if (!value) {
      loadData()
      search.value = null
      searchResults.value = null
      return
    }

    searchResults.value = []
    search.value = value
    loadData(true)
  }

  /**
   * Attempt to load data
   *
   * @param {Boolean} force
   *
   * @return {Void}
   */
  function loadData(force = false) {
    config.infinityRef.value.attemptLoad(force)
  }

  /**
   * Handle the infinity load response
   *
   * @param  {Object} data
   * @param  {String} associateable
   *
   * @return {Void}
   */
  function handleInfinityResult(data, associateable) {
    data.data.forEach(result => {
      let existsInStore =
        findIndex(record.value[associateable], ['id', Number(result.id)]) !== -1

      if (!existsInStore) {
        addResourceRecordHasManyRelationship(result, associateable)
      } else {
        updateResourceRecordHasManyRelationship(result, associateable)
      }
    })
  }

  /**
   * Make the request for data
   *
   * @param  {string} associateable
   * @param  {int} page
   * @param  {int|null} perPage
   *
   * @return {Promise}
   */
  function makeRequestForData(associateable, page, perPage) {
    return Innoclapps.request().get(`${record.value.path}/${associateable}`, {
      params: {
        page: page,
        q: search.value,
        timeline: 1,
        per_page: perPage || defaultPerPage.value,
        ...(config.requestParams || {}),
      },
    })
  }

  /**
   * Infinity load handler
   *
   * @param  {Object} $state
   * @param  {String} associateable
   *
   * @return {Void}
   */
  async function infiniteHandler($state, associateable) {
    // We must check if the user has the permissions to view the record
    // in order to load the recorable resource
    // Can happen when user creates e.q. contact and assign this contact
    // to another user but the user who created the contact has only permissions
    // to view his own contacts, in this case, we will still show the contact profile
    // but there will be a message tha this user will be unable to view the contact
    if (gate.denies('view', record.value)) {
      $state.complete()
      return
    }

    let data = null

    if (!config.makeRequestForData) {
      ({ data: data } = await makeRequestForData(associateable, page.value))
    } else {
      ({ data: data } = await config.makeRequestForData(
        associateable,
        page.value
      ))
    }

    if (data.data.length === 0) {
      if (isPerformingSearch.value) {
        // No search results and page is equal to 1?
        // In this case, just set the search results to empty
        if (page.value === 1) {
          searchResults.value = []
        }
      }

      $state.complete()
      dataLoadedFirstTime.value = true
      return
    }

    page.value += 1

    if (isPerformingSearch.value) {
      searchResults.value = !hasSearchResults.value
        ? data.data
        : searchResults.value.concat(...data.data)
    } else {
      if (config.handleInfinityResult) {
        config.handleInfinityResult(data, associateable)
      } else {
        handleInfinityResult(data, associateable)
      }

      nextTick(() => (dataLoadedFirstTime.value = true))
    }

    $state.loaded()
  }

  /**
   * Refresh the current recordable
   */
  function refresh(associateable) {
    makeRequestForData(
      associateable,
      1,
      defaultPerPage.value * page.value
    ).then(({ data }) => {
      if (data.data.length === 0) {
        resetRecordHasManyRelationship(associateable)

        return
      }

      handleInfinityResult(data, associateable)
    })
  }

  /**
   * Retrieve the given associateble resource and scroll the container to the node
   */
  async function focusToAssociateableElement(
    associateable,
    id,
    elementSectionPrefix
  ) {
    // We will first retrieve the associatebale record and add to the resource record
    // relationship object, as it may be old record and the associatables record are paginated
    // in this case, if we query the document directly the record may no exists in the document
    let { data: responseRecord } = await Innoclapps.request().get(
      `/${associateable}/${id}`,
      {
        params: {
          via_resource: config.resourceName,
          via_resource_id: record.value.id,
        },
      }
    )

    addResourceRecordHasManyRelationship(responseRecord, associateable)

    await nextTick()

    const tabPanelNode = document.getElementById('tabPanel-' + associateable)

    const recordNode = tabPanelNode.querySelector(
      `.${elementSectionPrefix}-${responseRecord.id}`
    )

    const scrollNode = config.scrollElement
      ? document.querySelector(config.scrollElement)
      : window

    if (recordNode) {
      scrollNode.scrollTo({
        top: recordNode.getBoundingClientRect().top,
        behavior: 'smooth',
      })
    }
  }

  return {
    record,
    focusToAssociateableElement,
    dataLoadedFirstTime,
    searchResults,
    hasSearchResults,
    infiniteHandler,
    search,
    loadData,
    performSearch,
    isPerformingSearch,
    defaultPerPage,
    refresh,
  }
}
