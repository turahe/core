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
import ITabGroupComponent from './ITabGroup.vue'
import ITabListComponent from './ITabList.vue'
import ITabComponent from './ITab.vue'
import ITabPanelsComponent from './ITabPanels.vue'
import ITabPanelComponent from './ITabPanel.vue'

const ITabsPlugin = {
  install(app) {
    app.component('ITabGroup', ITabGroupComponent)
    app.component('ITabList', ITabListComponent)
    app.component('ITab', ITabComponent)
    app.component('ITabPanels', ITabPanelsComponent)
    app.component('ITabPanel', ITabPanelComponent)
  },
}

// Components
export const ITabGroup = ITabGroupComponent
export const ITabList = ITabListComponent
export const ITab = ITabComponent
export const ITabPanels = ITabPanelsComponent
export const ITabPanel = ITabPanelComponent

// Plugin
export default ITabsPlugin
