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
import moment from 'moment-timezone'

// import other locales as they are added
import 'moment/dist/locale/pt-br'
import 'moment/dist/locale/es'
import 'moment/dist/locale/ru'

import momentPhp from './momentPhp'
import { getLocale } from '@/utils'

const getMomentLocale = () => getLocale().replace('_', '-')

// If the locale is not imported, will fallback to en
moment.locale(
  moment.locales().indexOf(getMomentLocale()) === -1 ? 'en' : getMomentLocale()
)

momentPhp(moment)

window.moment = moment
