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
export function useDraggable() {
  const draggableOptions = {
    delay: 15,
    delayOnTouchOnly: true,
    animation: 0,
    disabled: false,
    ghostClass: 'drag-ghost-rounded',
  }

  const scrollableDraggableOptions = {
    scroll: true,
    scrollSpeed: 50,
    forceFallback: true,
    ...draggableOptions.value,
  }

  return { draggableOptions, scrollableDraggableOptions }
}
