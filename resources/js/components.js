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
import { defineAsyncComponent } from 'vue'

import Notifications from 'notiwind'
import TheFloatNotifications from '~/Core/resources/js/components/TheFloatNotifications.vue'

import IButtonPlugin from '~/Core/resources/js/components/UI/Buttons'
import ICardPlugin from '~/Core/resources/js/components/UI/Card'
import IDropdownPlugin from '~/Core/resources/js/components/UI/Dropdown'
import IFormPlugin from '~/Core/resources/js/components/UI/Form'
import IDialogPlugin from '~/Core/resources/js/components/UI/Dialog'
import ITabsPlugin from '~/Core/resources/js/components/UI/Tabs'
import ITooltipPlugin from '~/Core/resources/js/components/UI/Tooltip'

import AuthLogin from '@/views/Auth/AuthLogin.vue'
import AuthPasswordEmail from '@/views/Auth/AuthPasswordEmail.vue'
import AuthPasswordReset from '@/views/Auth/AuthPasswordReset.vue'

import ActionPanel from '~/Core/resources/js/views/ActionPanel.vue'

import ISpinner from '~/Core/resources/js/components/UI/ISpinner.vue'
import IStepsCircle from '~/Core/resources/js/components/UI/Steps/IStepsCircle.vue'
import IStepCircle from '~/Core/resources/js/components/UI/Steps/IStepCircle.vue'
import IEmptyState from '~/Core/resources/js/components/UI/IEmptyState.vue'
import IPopover from '~/Core/resources/js/components/UI/IPopover.vue'
import ITable from '~/Core/resources/js/components/UI/ITable.vue'
import ILayout from '~/Core/resources/js/components/Layout.vue'
import IBadge from '~/Core/resources/js/components/UI/IBadge.vue'
import IColorSwatches from '~/Core/resources/js/components/UI/IColorSwatches.vue'
import IVerticalNavigation from '~/Core/resources/js/components/UI/VerticalNavigation/IVerticalNavigation.vue'
import IVerticalNavigationItem from '~/Core/resources/js/components/UI/VerticalNavigation/IVerticalNavigationItem.vue'
import ICustomSelect from '~/Core/resources/js/components/UI/CustomSelect/index'
import IAvatar from '~/Core/resources/js/components/UI/IAvatar.vue'
import IAlert from '~/Core/resources/js/components/UI/IAlert.vue'
import IOverlay from '~/Core/resources/js/components/UI/IOverlay.vue'
import IActionMessage from '~/Core/resources/js/components/UI/IActionMessage.vue'
import IIconPicker from '~/Core/resources/js/components/UI/IIconPicker.vue'

import Icon from '~/Core/resources/js/components/UI/Icon.vue'

import FieldsGenerator from '~/Core/resources/js/components/Fields/FieldsGenerator.vue'
import DropdownSelectInput from '~/Core/resources/js/components/DropdownSelectInput.vue'
import FieldsPlaceholder from '~/Core/resources/js/components/Fields/FieldsPlaceholder.vue'
import SearchInput from '~/Core/resources/js/components/SearchInput.vue'

import TheNavbar from '~/Core/resources/js/components/TheNavbar.vue'
import NavbarSeparator from '~/Core/resources/js/components/NavbarSeparator.vue'
import TheSidebar from '~/Core/resources/js/components/TheSidebar.vue'

import DatePicker from '~/Core/resources/js/components/DatePicker/DatePicker.vue'
import DateRangePicker from '~/Core/resources/js/components/DatePicker/DateRangePicker.vue'

import ActionDialog from '~/Core/resources/js/components/Actions/ActionsDialog.vue'
import ProgressionChart from '~/Core/resources/js/components/Charts/ProgressionChart.vue'
import PresentationChart from '~/Core/resources/js/components/Charts/PresentationChart.vue'

import Card from '~/Core/resources/js/components/Cards/Card.vue'
import CardTable from '~/Core/resources/js/components/Cards/CardTable.vue'
import CardTableAsync from '~/Core/resources/js/components/Cards/CardTableAsync.vue'

import PreviewModal from '~/Core/resources/js/components/PreviewModal.vue'
import Editor from '~/Core/resources/js/components/Editor'

const TextCollapse = defineAsyncComponent(() =>
  import('~/Core/resources/js/components/TextCollapse.vue')
)

export default function (app) {
  app
    .use(Notifications)
    .use(IButtonPlugin)
    .use(ICardPlugin)
    .use(IDropdownPlugin)
    .use(IFormPlugin)
    .use(IDialogPlugin, { globalEmitter: Innoclapps })
    .use(ITabsPlugin)
    .use(ITooltipPlugin)

  app
    .component('ILayout', ILayout)
    .component('IActionMessage', IActionMessage)
    .component('IAvatar', IAvatar)
    .component('ITable', ITable)
    .component('ICustomSelect', ICustomSelect)
    .component('IOverlay', IOverlay)
    .component('IPopover', IPopover)
    .component('IEmptyState', IEmptyState)
    .component('IIconPicker', IIconPicker)
    .component('ISpinner', ISpinner)
    .component('IStepsCircle', IStepsCircle)
    .component('IStepCircle', IStepCircle)
    .component('IColorSwatches', IColorSwatches)
    .component('IVerticalNavigation', IVerticalNavigation)
    .component('IVerticalNavigationItem', IVerticalNavigationItem)
    .component('IAlert', IAlert)
    .component('IBadge', IBadge)

  app.component('TheNavbar', TheNavbar)
  app.component('NavbarSeparator', NavbarSeparator)
  app.component('TheSidebar', TheSidebar)

  app.component('TheFloatNotifications', TheFloatNotifications)

  app.component('DatePicker', DatePicker)
  app.component('DateRangePicker', DateRangePicker)

  app.component('AuthLogin', AuthLogin)
  app.component('AuthPasswordEmail', AuthPasswordEmail)
  app.component('AuthPasswordReset', AuthPasswordReset)

  app.component('ActionPanel', ActionPanel)

  app.component('Icon', Icon)

  app.component('ActionDialog', ActionDialog)

  app.component('ProgressionChart', ProgressionChart)
  app.component('PresentationChart', PresentationChart)
  app.component('CardTable', CardTable)
  app.component('CardTableAsync', CardTableAsync)
  app.component('Card', Card)
  app.component('PreviewModal', PreviewModal)

  app.component('TextCollapse', TextCollapse)
  app.component('Editor', Editor)

  app.component('FieldsGenerator', FieldsGenerator)
  app.component('FieldsPlaceholder', FieldsPlaceholder)

  app.component('DropdownSelectInput', DropdownSelectInput)
  app.component('SearchInput', SearchInput)
}
