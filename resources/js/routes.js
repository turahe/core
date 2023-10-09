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
import i18n from '~/Core/resources/js/i18n'

// General routes

import Dashboard from './views/Dashboard/DashboardIndex.vue'
import EditDashboard from './views/Dashboard/EditDashboard.vue'

import OAuthAccounts from './views/OAuth/OAuthAccounts.vue'

import NotificationIndex from './views/Notifications/NotificationIndex.vue'

import TrashedResourceRecords from './views/Resources/TrashedResourceRecords.vue'
import ResourceImport from './views/Resources/ImportResource.vue'

import Error404 from './views/Error404.vue'
import Error403 from './views/Error403.vue'

// Settings general routes

import SettingsIndex from '~/Core/resources/js/views/Settings/Settings.vue'
import SettingsGeneral from '~/Core/resources/js/views/Settings/SettingsGeneral.vue'

import SettingsUpdate from '~/Core/resources/js/views/Settings/System/SettingsUpdate.vue'
import SettingsTools from '~/Core/resources/js/views/Settings/System/SettingsTools.vue'
import SettingsSystemInfo from '~/Core/resources/js/views/Settings/System/SettingsSystemInfo.vue'
import SettingsSystemLogs from '~/Core/resources/js/views/Settings/System/SettingsSystemLogs.vue'

import SettingsSecurity from '~/Core/resources/js/views/Settings/Security/SettingsSecurity.vue'
import SettingsRecaptcha from '~/Core/resources/js/views/Settings/Security/SettingsRecaptcha.vue'
import SettingsMailableTemplates from '~/Core/resources/js/views/Settings/SettingsMailableTemplates.vue'
import SettingsWorkflows from '~/Core/resources/js/views/Workflows/WorkflowList.vue'
import SettingsFields from '~/Core/resources/js/views/Settings/Fields/SettingsFields.vue'

// Settings integration routes

import SettingsMicrosoft from './views/Settings/Integrations/SettingsMicrosoft.vue'
import SettingsGoogle from './views/Settings/Integrations/SettingsGoogle.vue'
import SettingsPusher from './views/Settings/Integrations/SettingsPusher.vue'
import SettingsTwilio from './views/Settings/Integrations/SettingsTwilio.vue'
import SettingsZapier from './views/Settings/Integrations/SettingsZapier.vue'

const routes = [
  {
    alias: '/',
    path: '/dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/dashboard/:id/edit',
    name: 'edit-dashboard',
    component: EditDashboard,
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationIndex,
    meta: {
      title: i18n.t('core::notifications.your'),
    },
  },
  {
    path: '/import/:resourceName',
    name: 'import-resource',
    component: ResourceImport,
  },
  {
    path: '/oauth/accounts',
    name: 'oauth-accounts',
    component: OAuthAccounts,
    meta: {
      title: i18n.t('core::oauth.connected_accounts'),
    },
  },
  {
    path: '/trashed/:resourceName',
    name: 'trashed-resource-records',
    component: TrashedResourceRecords,
    meta: {
      title: i18n.t('core::app.soft_deletes.trashed_records'),
    },
  },
  {
    name: '404',
    path: '/404',
    component: Error404,
  },
  {
    name: '403',
    path: '/403',
    component: Error403,
  },
  {
    name: 'not-found',
    path: '/:pathMatch(.*)*',
    component: Error404,
  },

  // Settings routes
  {
    path: '/settings',
    name: 'settings',
    component: SettingsIndex,
    meta: {
      title: i18n.t('core::settings.settings'),
      gate: 'is-super-admin',
    },
    children: [
      {
        path: 'general',
        component: SettingsGeneral,
        name: 'settings-general',
        meta: { title: i18n.t('core::settings.general_settings') },
        alias: '/settings',
      },
      {
        path: 'fields/:resourceName',
        name: 'resource-fields',
        component: SettingsFields,
      },
      // Integration routes
      {
        path: 'integrations/microsoft',
        component: SettingsMicrosoft,
        name: 'settings-integrations-microsoft',
        meta: {
          title: 'Microsoft',
        },
      },
      {
        path: 'integrations/google',
        component: SettingsGoogle,
        name: 'settings-integrations-google',
        meta: {
          title: 'Google',
        },
      },
      {
        path: 'integrations/pusher',
        component: SettingsPusher,
        name: 'settings-integrations-pusher',
        meta: {
          title: 'Pusher',
        },
      },
      {
        path: 'integrations/twilio',
        component: SettingsTwilio,
        name: 'settings-integrations-twilio',
        meta: {
          title: 'Twilio',
        },
      },
      {
        path: 'integrations/zapier',
        component: SettingsZapier,
        name: 'settings-integrations-zapier',
        meta: {
          title: 'Zapier',
        },
      },
      {
        path: '/settings/workflows',
        component: SettingsWorkflows,
        meta: { title: i18n.t('core::workflow.workflows') },
      },
      {
        path: '/settings/mailables',
        component: SettingsMailableTemplates,
        meta: { title: i18n.t('core::mail_template.mail_templates') },
      },
      {
        path: '/settings/update',
        component: SettingsUpdate,
        name: 'update',
        meta: { title: i18n.t('core::update.system') },
      },
      {
        path: '/settings/tools',
        component: SettingsTools,
        meta: { title: i18n.t('core::settings.tools.tools') },
      },
      {
        path: '/settings/info',
        component: SettingsSystemInfo,
        meta: { title: i18n.t('core::app.system_info') },
      },
      {
        path: '/settings/logs',
        component: SettingsSystemLogs,
        meta: { title: 'Logs' },
      },
      {
        path: '/settings/security',
        component: SettingsSecurity,
        meta: { title: i18n.t('core::settings.security.security') },
      },
      {
        path: '/settings/recaptcha',
        component: SettingsRecaptcha,
        meta: { title: i18n.t('core::settings.recaptcha.recaptcha') },
      },
    ],
  },
]

export default routes
