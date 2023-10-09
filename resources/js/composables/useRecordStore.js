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
import castArray from 'lodash/castArray'

export function useRecordStore(storeName = 'record', linkedStoreName = null) {
  const store = useStore()

  const record = computed(() => store.state[unref(storeName)]?.record || {})

  /**
   * @param {Object} record
   * @param {String} resourceName
   * @param {Boolean} possiblePreview
   */
  function ensureRecordIsUpdated(record, resourceName, possiblePreview) {
    if (!possiblePreview) {
      setRecord(record)
    } else {
      store.commit('recordPreview/SET_RECORD', record)
      // When previewing the same record in profile view
      // update the main store to reflect the updates as well
      if (
        store.state.recordPreview.record.id == record.id &&
        store.state.recordPreview.viaResource === resourceName
      ) {
        setRecord(record)
      }
    }
  }

  /**
   * Set resource record data
   *
   * @param  {Object} data
   *
   * @return {Void}
   */
  function setRecord(data) {
    store.commit(unref(storeName) + '/SET_RECORD', data)
  }

  /**
   * Reset the resource record
   *
   * @return {Void}
   */
  function resetRecord() {
    store.commit(unref(storeName) + '/RESET_RECORD')
  }

  /**
   * Add resource record media
   *
   * @param  {Object} media
   *
   * @return {Void}
   */
  function addResourceRecordMedia(media) {
    addResourceRecordHasManyRelationship(media, 'media')
  }

  /**
   * Remove resource record media
   *
   * @param  {Object} media
   *
   * @return {Void}
   */
  function removeResourceRecordMedia(media) {
    removeResourceRecordHasManyRelationship(media.id, 'media')
  }

  /**
   * Add resource record relationship
   *
   * @param  {Object} record
   * @param  {String} relation
   * @param  {Boolean} force
   *
   * @return {Void}
   */
  function addResourceRecordHasManyRelationship(
    record,
    relation,
    force = false
  ) {
    store.commit(storeName + '/ADD_RECORD_HAS_MANY_RELATIONSHIP', {
      relation: relation,
      item: record,
      force: force,
    })

    if (linkedStoreName && store.state.recordPreview.viaResource) {
      store.commit(linkedStoreName + '/ADD_RECORD_HAS_MANY_RELATIONSHIP', {
        relation: relation,
        item: record,
        force: force,
      })
    }
  }

  /**
   * Add resource record relationship
   *
   * @param  {String} relation
   * @param  {Array} items
   *
   * @return {Void}
   */
  function setResourceRecordHasManyRelationship(relation, items) {
    store.commit(storeName + '/SET_RECORD_HAS_MANY_RELATIONSHIP', {
      relation,
      items,
    })
  }

  /**
   * Update single resource record relationship
   *
   * @param  {Object} record
   * @param  {String} relation
   *
   * @return {Void}
   */
  function updateResourceRecordHasManyRelationship(record, relation) {
    store.commit(storeName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP', {
      relation: relation,
      id: record.id,
      item: record,
    })

    if (linkedStoreName && store.state.recordPreview.viaResource) {
      store.commit(linkedStoreName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP', {
        relation: relation,
        id: record.id,
        item: record,
      })
    }
  }

  /**
   * Remove single relationship from resource record
   *
   * @param  {Number} id
   * @param  {String} relation
   *
   * @return {Void}
   */
  function removeResourceRecordHasManyRelationship(id, relation) {
    store.commit(storeName + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP', {
      relation: relation,
      id: id,
    })

    if (linkedStoreName && store.state.recordPreview.viaResource) {
      store.commit(linkedStoreName + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP', {
        relation: relation,
        id: id,
      })
    }
  }

  /**
   * Reset the record in store has many relationship
   *
   * @param  {String} relation
   *
   * @return {Void}
   */
  function resetRecordHasManyRelationship(relation) {
    store.commit(storeName + '/RESET_RECORD_HAS_MANY_RELATIONSHIP', relation)
  }

  /**
   * Add resource record sub relation
   *
   * @param {string} relation
   * @param {Number} relationId
   * @param {String} subRelation
   * @param {Object} record
   */
  function addResourceRecordSubRelation(
    relation,
    relationId,
    subRelation,
    record
  ) {
    store.commit(storeName + '/ADD_RECORD_HAS_MANY_SUB_RELATION', {
      relation: relation,
      relation_id: relationId,
      sub_relation: subRelation,
      item: record,
    })

    if (linkedStoreName && store.state.recordPreview.viaResource) {
      store.commit(linkedStoreName + '/ADD_RECORD_HAS_MANY_SUB_RELATION', {
        relation: relation,
        relation_id: relationId,
        sub_relation: subRelation,
        item: record,
      })
    }
  }

  /**
   * Update resource record sub relation
   *
   * @param {string} relation
   * @param {Number} relationId
   * @param {String} subRelation
   * @param {Object} record
   */
  function updateResourceRecordSubRelation(
    relation,
    relationId,
    subRelation,
    record
  ) {
    store.commit(storeName + '/UPDATE_RECORD_HAS_MANY_SUB_RELATION', {
      relation: relation,
      relation_id: relationId,
      sub_relation: subRelation,
      sub_relation_id: record.id,
      item: record,
    })
  }

  /**
   * Add resource record sub relation
   *
   * @param {string} relation
   * @param {Number} relationId
   * @param {String} subRelation
   * @param {Number} subRelationId
   */
  function removeResourceRecordSubRelation(
    relation,
    relationId,
    subRelation,
    subRelationId
  ) {
    store.commit(storeName + '/REMOVE_RECORD_HAS_MANY_SUB_RELATION', {
      relation: relation,
      relation_id: relationId,
      sub_relation: subRelation,
      sub_relation_id: subRelationId,
    })

    if (linkedStoreName && store.state.recordPreview.viaResource) {
      store.commit(linkedStoreName + '/REMOVE_RECORD_HAS_MANY_SUB_RELATION', {
        relation: relation,
        relation_id: relationId,
        sub_relation: subRelation,
        sub_relation_id: subRelationId,
      })
    }
  }

  /**
   * Decrement the resource record count property
   *
   * @param {String|Array} key
   *
   * @return {Void}
   */
  function decrementResourceRecordCount(key) {
    castArray(key).forEach(key => {
      if (Number(record.value[key]) > 0) {
        store.commit(storeName + '/SET_RECORD', {
          [key]: record.value[key] - 1,
        })
      }
    })
  }

  /**
   * Increment the resource record count property
   *
   * @param {String|Array} key
   *
   * @return {Void}
   */
  function incrementResourceRecordCount(key) {
    castArray(key).forEach(key => {
      store.commit(storeName + '/SET_RECORD', {
        [key]: record.value[key] + 1,
      })
    })
  }

  return {
    incrementResourceRecordCount,
    decrementResourceRecordCount,
    removeResourceRecordSubRelation,
    updateResourceRecordSubRelation,
    addResourceRecordSubRelation,
    removeResourceRecordHasManyRelationship,
    updateResourceRecordHasManyRelationship,
    setResourceRecordHasManyRelationship,
    addResourceRecordHasManyRelationship,
    resetRecordHasManyRelationship,
    removeResourceRecordMedia,
    addResourceRecordMedia,
    ensureRecordIsUpdated,
    setRecord,
    resetRecord,
    record,
  }
}
