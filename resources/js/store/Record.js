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
import ResourceCrud from '@/store/actions/ResourceCrud'

const state = {
  record: {},
}

const mutations = {
  ...ResourceMutations,
}

const actions = {
  ...ResourceCrud,
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
}
