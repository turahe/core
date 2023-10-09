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
import { ref } from 'vue'

export function useLoader(defaultValue = false) {
  const isLoading = ref(defaultValue)

  function setLoading(value = true) {
    isLoading.value = value
  }

  return { setLoading, isLoading }
}
