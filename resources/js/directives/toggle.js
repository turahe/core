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
  beforeMount: function (el, binding) {
    el._toggle = e => {
      const toggleElement = document.getElementById(binding.value)
      if (
        toggleElement.style.display === 'none' ||
        toggleElement.classList.contains('hidden')
      ) {
        toggleElement.style.display = 'block'
        toggleElement.classList.remove('hidden')
      } else {
        toggleElement.style.display = 'none'
      }
    }

    el.addEventListener('click', el._toggle)
  },
  unmounted: function (el, binding) {
    el.removeEventListener('click', el._toggle)
  },
}
