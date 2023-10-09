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
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
window.Pusher = Pusher

export default class Broadcast {
  constructor(config) {
    this.config = config

    if (this.hasDriver()) {
      this.configure()
    }
  }

  /**
   * Configure the Laravel echo instance
   *
   * @return {Void}
   */
  configure() {
    window.Echo = new Echo(this.getDriverConfig())
  }

  /**
   * Get the broadcast driver config
   *
   * @return {Object}
   */
  getDriverConfig() {
    return {
      broadcaster: this.config.default,
      ...this.getConfigByDriver(this.config.default),
    }
  }

  /**
   * Check whether broadcasting driver is configured
   *
   * The function excluded the log and null drivers
   * as these drivers are not applicable for the front-end
   *
   * @return {Boolean}
   */
  hasDriver() {
    return (
      this.config.default &&
      this.config.default !== 'log' &&
      this.config.default !== 'null'
    )
  }

  /**
   * Get the connection configuration
   *
   * @param  {String} connection
   *
   * @return {Object|null}
   */
  getConfigByDriver(connection) {
    if (connection === 'pusher') {
      return {
        key: this.config.connection.key,
        cluster: this.config.connection.options.cluster,
        encrypted: this.config.connection.options.encrypted,
        authorizer: (channel, options) => {
          return {
            authorize: (socketId, callback) => {
              Innoclapps.request()
                .post(
                  '/broadcasting/auth',
                  {
                    socket_id: socketId,
                    channel_name: channel.name,
                  },
                  {
                    baseURL: '/',
                  }
                )
                .then(response => {
                  callback(false, response.data)
                })
                .catch(error => {
                  callback(true, error)
                })
            },
          }
        },
      }
    }

    // @todo for other connections if necessary
  }
}
