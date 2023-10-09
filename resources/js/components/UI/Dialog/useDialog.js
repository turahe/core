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
import { onMounted, unref } from 'vue'
import { useGlobalEventListener } from '~/Core/resources/js/composables/useGlobalEventListener'

export function useDialog(show, hide, dialogId) {
  function globalShow(id) {
    if (id === unref(dialogId)) {
      show()
    }
  }

  function globalHide(id) {
    if (id === unref(dialogId)) {
      hide()
    }
  }

  onMounted(() => {
    useGlobalEventListener('modal-hide', globalHide)
    useGlobalEventListener('modal-show', globalShow)
  })
}
