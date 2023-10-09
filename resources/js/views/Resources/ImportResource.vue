<!--
  - This source code is the proprietary and confidential information of
  - Nur Wachid. You may not disclose, copy, distribute,
  -  or use this code without the express written permission of
  - Nur Wachid.
  -
  - Copyright (c) 2023.
  -
  -
  -->

<template>
  <ILayout>
    <Import :resource-name="$route.params.resourceName" />
  </ILayout>
</template>
<script setup>
import Import from '~/Core/resources/js/components/Import'
import { useI18n } from 'vue-i18n'
import { useApp } from '~/Core/resources/js/composables/useApp'

const { t } = useI18n()
const { setPageTitle } = useApp()

setPageTitle(t('core::import.import_records'))
</script>
