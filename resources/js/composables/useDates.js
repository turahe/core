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
import { computed } from 'vue'
import isString from 'lodash/isString'
import { useApp } from './useApp'

export function useDates() {
  const { currentUser, setting } = useApp()

  /**
   * Determine if the user is using 12-hour time
   */
  const usesTwelveHourTime = computed(() => {
    return currentTimeFormat.value.indexOf('H:i') === -1
  })

  /**
   * Get the user's local timezone.
   */
  const userTimezone = computed(() => {
    return currentUser.value && currentUser.value.timezone
      ? currentUser.value.timezone
      : moment.tz.guess()
  })

  /**
   * Determine the application current time format
   */
  const currentTimeFormat = computed(() => {
    return currentUser.value
      ? currentUser.value.time_format
      : setting('time_format')
  })

  /**
   * Determine the application current date format
   */
  const currentDateFormat = computed(() => {
    return currentUser.value
      ? currentUser.value.date_format
      : setting('date_format')
  })

  /**
   * Converts the PHP options date format to moment compatible
   */
  const dateTimeFormatForMoment = computed(() => {
    return moment().PHPconvertFormat(
      currentDateFormat.value + ' ' + currentTimeFormat.value
    )
  })

  /**
   * Converts the PHP options date format to moment compatible
   */
  const dateFormatForMoment = computed(() => {
    return moment().PHPconvertFormat(currentDateFormat.value)
  })

  /**
   * Convert the given localized date time string to the application's timezone.
   */
  function dateToAppTimezone(value, format = 'YYYY-MM-DD HH:mm:ss') {
    return value
      ? moment
          .tz(value, userTimezone.value)
          .clone()
          .tz(Innoclapps.config('timezone'))
          .format(format)
      : value
  }

  /**
   * Convert the given application timezone date time string to the local timezone.
   */
  function dateFromAppTimezone(value, format = 'YYYY-MM-DD HH:mm:ss') {
    if (!value) {
      return value
    }

    return appMoment(value).clone().tz(userTimezone.value).format(format)
  }

  /**
   * Get the localized date by UTC/App default datetime
   */
  function localizedDateTime(value, format) {
    if (!value) {
      return value
    }

    return appMoment(value)
      .clone()
      .tz(userTimezone.value)
      .format(format || dateTimeFormatForMoment.value)
  }

  /**
   * Get the localized date by UTC/App default date
   */
  function localizedDate(value, format) {
    if (!value) {
      return value
    }

    const instance = appMoment(value).clone();

    if(isISODate(value) && !/T00:00:00.000000Z$/.test(value)) {
      instance.tz(userTimezone.value);
    }

    return instance.format(format || dateFormatForMoment.value)
  }

  /**
   * Get app date now with app timezone set
   */
  function appMoment(value) {
    return moment.tz(value, Innoclapps.config('timezone'))
  }

  /**
   * Get current application time and date
   */
  function appDate(format = 'YYYY-MM-DD HH:mm:ss') {
    return appMoment().format(format)
  }

  /**
   * Determine if the given string is date time in ISO format
   */
  function isISODate(str) {
    // First perform the checks below, less IQ
    if (!isString(str)) {
      return false
    }

    if (str.indexOf('-') === 1) {
      return false
    }

    // 2020-04-02T03:39:56.000000Z
    return /\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3,6}Z/.test(str)
  }

  /**
   * Determine if the given string is standard date time
   */
  function isStandardDateTime(str) {
    // First perform the checks below, less IQ
    if (!isString(str)) {
      return false
    }

    if (
      str.indexOf('-') <= 1 ||
      str.indexOf(' ') === 0 ||
      str.indexOf(':') === 0
    ) {
      return false
    }

    return /\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(str)
  }

  /**
   * Determine if the given string is date
   */
  function isDate(str) {
    // First perform the checks below, less IQ
    if (!isString(str)) {
      return false
    }

    if (str.indexOf('-') === 1) {
      return false
    }

    return /\d{4}-\d{2}-\d{2}$/.test(str)
  }

  /**
   * Maybe format date
   */
  function maybeFormatDateValue(value, fallbackValue = null) {
    if (isISODate(value)) {
      if (/T00:00:00.000000Z$/.test(value)) {
        return localizedDate(value)
      }

      return localizedDateTime(value)
    } else if (isDate(value)) {
      return localizedDate(value)
    } else if (isStandardDateTime(value)) {
      return localizedDateTime(value)
    }

    return fallbackValue
  }

  return {
    appDate,
    appMoment,
    localizedDate,
    localizedDateTime,
    dateFromAppTimezone,
    dateToAppTimezone,
    dateFormatForMoment,
    dateTimeFormatForMoment,
    currentDateFormat,
    currentTimeFormat,
    userTimezone,
    usesTwelveHourTime,
    maybeFormatDateValue,

    isISODate,
    isStandardDateTime,
    isDate
  }
}
