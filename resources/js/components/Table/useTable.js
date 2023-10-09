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
import { useStore } from 'vuex'

export function useTable() {
  const store = useStore()

  function reloadTable(tableId) {
    Innoclapps.$emit('reload-resource-table', tableId)
  }

  function customizeTable(tableId, value = true) {
    store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
      id: tableId,
      value: value,
    })
  }

  return { reloadTable, customizeTable }
}
