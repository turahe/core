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
import { ref, computed } from 'vue'
import orderBy from 'lodash/orderBy'
import { createGlobalState } from '@vueuse/core'

export const useTags = createGlobalState(() => {
  const tags = ref([])

  const tagsByDisplayOrder = computed(() =>
    orderBy(tags.value, 'display_order')
  )

  function idx(id) {
    return tags.value.findIndex(tag => tag.id == id)
  }

  function findTagById(id) {
    return tagsByDisplayOrder.value.find(t => t.id == id)
  }

  function findTagsByType(type) {
    return tagsByDisplayOrder.value.filter(t => t.type == type)
  }

  function setTags(list) {
    tags.value = list
  }

  function addTag(tag) {
    tags.value.push(tag)
  }

  function setTag(id, tag) {
    tags.value[idx(id)] = tag
  }

  function removeTag(id) {
    tags.value.splice(idx(id), 1)
  }

  return {
    tags,
    tagsByDisplayOrder,

    findTagById,
    findTagsByType,
    setTags,
    setTag,
    addTag,
    removeTag,
  }
})
