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
import { formatMoney, formatNumber, toFixed } from 'accounting-js'

export function useAccounting(value) {
  return {
    toFixed,
    formatNumber: function (value) {
      return formatNumber(value, {
        precision: Innoclapps.config('currency.precision'),
        thousand: Innoclapps.config('currency.thousands_separator'),
        decimal: Innoclapps.config('currency.decimal_mark'),
      })
    },
    formatMoney: function (value) {
      return formatMoney(value, {
        symbol: Innoclapps.config('currency.symbol'),
        precision: Innoclapps.config('currency.precision'),
        thousand: Innoclapps.config('currency.thousands_separator'),
        decimal: Innoclapps.config('currency.decimal_mark'),
        format:
          Innoclapps.config('currency.symbol_first') == true ? '%s%v' : '%v%s',
      })
    },
  }
}
