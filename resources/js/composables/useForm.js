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
import { reactive } from 'vue'
import Form from '~/Core/resources/js/services/Form/Form'

export function useForm(data = {}, options = {}) {
  const form = reactive(new Form(data, options))

  return { form }
}
