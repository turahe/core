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
// Parents selector
Element.prototype.parents = function (selector) {
  var elements = []
  var elem = this
  var ishaveselector = selector !== undefined

  while ((elem = elem.parentElement) !== null) {
    if (elem.nodeType !== Node.ELEMENT_NODE) {
      continue
    }

    if (!ishaveselector || elem.matches(selector)) {
      elements.push(elem)
    }
  }

  return elements
}

function getElParentDialog(el) {
  return el.parents('.dialog')[0] || null
}

Element.prototype.parentDialogIdHash = function () {
  let dialogEl = getElParentDialog(this)

  if (!dialogEl) {
    return ''
  }

  // headless ui adds ID to the .dialog element, in this case, we will use this ID to mount the popover
  return '#' + dialogEl.getAttribute('id')
}

Element.prototype.inDialog = function () {
  return getElParentDialog(this) ? true : false
}
