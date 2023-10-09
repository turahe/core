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
import { ref } from 'vue'
export function useTextInput(elRef, emit, currentValue) {
  const valueWhenFocus = ref(null)

  function blurHandler(e) {
    emit('blur', e)

    if (currentValue.value !== valueWhenFocus.value) {
      emit('change', currentValue.value)
    }
  }

  function focusHandler(e) {
    emit('focus', e)

    valueWhenFocus.value = currentValue.value
  }

  function keyupHandler(e) {
    emit('keyup', e)
  }

  function keydownHandler(e) {
    emit('keydown', e)
  }

  function blur() {
    elRef.value.blur()
  }

  function click() {
    elRef.value.click()
  }

  function focus(options) {
    elRef.value.focus(options)
  }

  function select() {
    elRef.value.select()
  }

  function setRangeText(replacement) {
    elRef.value.setRangeText(replacement)
  }

  return {
    setRangeText,
    select,
    focus,
    click,
    blur,
    keydownHandler,
    keyupHandler,
    blurHandler,
    focusHandler,
    valueWhenFocus,
  }
}
