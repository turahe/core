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
import Modal from './modal'
import Toggle from './toggle'

function registerDirectives(app) {
  app.directive('i-modal', Modal)
  app.directive('i-toggle', Toggle)
}

export default registerDirectives
export { Modal, Toggle }
