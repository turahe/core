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
import { ref, shallowRef, nextTick, readonly } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { useForm } from '~/Core/resources/js/composables/useForm'

export function useSettings() {
  const { t } = useI18n()
  const store = useStore()
  const isReady = ref(false)
  const { form } = useForm()
  const originalSettings = shallowRef({})

  function fetchAndSetSettings() {
    Innoclapps.request()
      .get('/settings')
      .then(({ data }) => {
        form.set(data)
        originalSettings.value = data
        isReady.value = true
      })
  }

  function saveSettings(callback) {
    form.post('settings').then(settings => {
      Innoclapps.success(t('core::settings.updated'))

      if (typeof callback === 'function') {
        callback(form, settings)
      }

      form.keys().forEach(key => {
        if (Innoclapps.appConfig.options.hasOwnProperty(key)) {
          Innoclapps.appConfig.options[key] = form[key]
          store.commit('SET_SETTINGS', { [key]: form[key] })
        }
      })
    })
  }

  async function submit(callback) {
    // Wait till v-model update e.q. on checkboxes like in company field @change="submit"
    await nextTick()

    saveSettings(callback)
  }

  fetchAndSetSettings()

  return {
    isReady,
    submit,
    saveSettings,
    originalSettings: readonly(originalSettings),
    form,
  }
}
