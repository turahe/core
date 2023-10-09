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
export default {
  operand: { required: true },
  isNullable: { required: true, type: Boolean },
  index: { required: true, type: Number },
  query: { type: Object, required: false },
  rule: { type: Object, required: true },
  labels: { required: true },
  operator: { required: true },
  isBetween: { default: false, type: Boolean },
  readOnly: { default: false, type: Boolean },
}
