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
import TooltipComponent from './ITooltip.vue'
import tooltipDirective from './iTooltipDirective'
import RenderComponent from './utils/renderComponent'

const ITooltipPlugin = {
  install(app, options = {}) {
    app.component('ITooltip', TooltipComponent)
    app.directive('i-tooltip', tooltipDirective)

    const tooltipComponent = new RenderComponent({
      el: document.body,
      rootComponent: TooltipComponent,
      props: options,
      appContext: app._context,
    })

    tooltipComponent.mount()
  },
}

// Component
export const ITooltip = TooltipComponent

// Directive
export const Tooltip = tooltipDirective

// Plugin
export default ITooltipPlugin
