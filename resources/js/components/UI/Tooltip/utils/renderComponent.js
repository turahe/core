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
import { createVNode, render } from 'vue'

export default class RenderComponent {
  constructor(options) {
    this.el = options.el
    this.rootComponent = options.rootComponent
    this.props = options?.props ?? {}
    this.appContext = { ...(options?.appContext ?? {}) }
  }

  mount() {
    const componentVNode = createVNode(this.rootComponent, this.props)
    render(componentVNode, this.el)
  }

  unmount() {
    render(null, this.el)
  }
}
