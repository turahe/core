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
  beforeMount: function (el, binding, vnode) {
    el._showModal = () => {
      Innoclapps.$emit('modal-show', binding.value)
    }
    el.addEventListener('click', el._showModal)
  },
  unmounted: function (el, binding, vnode) {
    el.removeEventListener('click', el._showModal)
  },
}
