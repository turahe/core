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
export function useVoip() {
  const voip = Innoclapps.app.config.globalProperties.$voip

  const hasVoIPClient = Innoclapps.config('voip.client') !== null

  return { voip, hasVoIPClient }
}
