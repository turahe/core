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
import axios from 'axios'

const instance = axios.create()

instance.defaults.withCredentials = true
instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
instance.defaults.headers.common['Content-Type'] = 'application/json'

instance.interceptors.request.use(config => {
  // https://stackoverflow.com/questions/49435859/broadcast-to-others-and-dont-broadcast-to-current-user-are-not-working
  if (window.Echo) {
    let socketId = window.Echo.socketId()

    if (socketId) {
      config.headers['X-Socket-ID'] = window.Echo.socketId()
    }
  }

  return config
})

instance.interceptors.response.use(
  response => {
    return response
  },
  error => {
    if (axios.isCancel(error)) {
      return error
    }

    error.isValidationError = () => false

    const status = error.response.status

    if (status === 404) {
      // 404 not found
      Innoclapps.$emit('error-404')
    } else if (status === 403) {
      // Forbidden
      Innoclapps.$emit('error-403', error)
    } else if (status === 401) {
      // Session timeout / Logged out
      window.location.href = Innoclapps.config('url') + '/login'
    } else if (status === 409) {
      // Conflicts
      Innoclapps.$emit('conflict', error.response.data.message)
    } else if (status === 419) {
      // Handle expired CSRF token
      Innoclapps.$emit('token-expired', error)
    } else if (status === 422) {
      error.isValidationError = () => true
      // Emit form validation errors event
      Innoclapps.$emit('form-validation-errors', error.response.data.errors)
    } else if (status === 429) {
      // Handle throttle errors
      Innoclapps.$emit('too-many-requests', error)
    } else if (status === 503) {
      Innoclapps.$emit('maintenance-mode', error.response.data.message)
    } else if (status >= 500) {
      // 500 errors
      Innoclapps.$emit('error', error.response.data.message)
    }

    // Do something with response error
    return Promise.reject(error)
  }
)

export default instance
export const CancelToken = axios.CancelToken
