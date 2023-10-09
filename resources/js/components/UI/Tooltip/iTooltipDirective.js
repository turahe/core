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
const VALID_PLACEMENTS = [
  'top',
  'top-start',
  'top-end',
  'right',
  'right-start',
  'right-end',
  'bottom',
  'bottom-start',
  'bottom-end',
  'left',
  'left-start',
  'left-end',
]

const VALID_VARIANTS = ['dark', 'light']

function findModifier(availableModifiers, modifiers) {
  return availableModifiers.reduce((acc, cur) => {
    if (modifiers[cur]) acc = cur
    return acc
  }, '')
}

const updateAttributes = (el, binding) => {
  const { modifiers, value } = binding

  if (!value) return

  const placement = findModifier(VALID_PLACEMENTS, modifiers) || 'top'
  const variant = findModifier(VALID_VARIANTS, modifiers) || 'dark'

  el.setAttribute('v-placement', placement)
  el.setAttribute('v-tooltip', value)
  el.setAttribute('v-variant', variant)
}

export default {
  beforeMount: (el, binding) => updateAttributes(el, binding),
  updated: (el, binding) => updateAttributes(el, binding),
  beforeUnmount(el) {
    el.removeAttribute('v-tooltip')
    el.removeAttribute('v-placement')
    el.removeAttribute('v-variant')
  },
}
