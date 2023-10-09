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
  <ISlideover
    :visible="true"
    @hidden="$router.back"
    @shown="handleModalShown"
    :ok-disabled="form.busy"
    :ok-title="$t('core::app.create')"
    form
    @submit="save"
    @keydown="form.onKeydown($event)"
    :title="$t('core::role.create')"
    static-backdrop
  >
    <RoleFormFields ref="formRef" :form="form" :create="true" />
  </ISlideover>
</template>
<script setup>
import { ref } from 'vue'
import RoleFormFields from './RoleFormFields.vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { useForm } from '~/Core/resources/js/composables/useForm'
import { useRoles } from './useRoles'

const { t } = useI18n()

const formRef = ref(null)
const router = useRouter()
const { addRole } = useRoles()

const { form } = useForm({
  name: null,
  permissions: [],
})

function handleModalShown() {
  formRef.value.nameRef.focus()
}

function save() {
  form.post('/roles').then(role => {
    addRole(role)
    Innoclapps.success(t('core::role.created'))
    router.back()
  })
}
</script>
