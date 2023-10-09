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
    @hidden="$router.back"
    :visible="true"
    :ok-title="$t('core::app.save')"
    :ok-disabled="form.busy"
    form
    @submit="update"
    @keydown="form.onKeydown($event)"
    :title="modalTitle"
    static-backdrop
  >
    <FieldsPlaceholder v-if="!componentReady" />

    <RoleFormFields v-else :form="form" />
  </ISlideover>
</template>
<script setup>
import { ref, computed, nextTick } from 'vue'
import RoleFormFields from './RoleFormFields.vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from '~/Core/resources/js/composables/useForm'
import { useRoles } from './useRoles'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const { fetchRole, setRole } = useRoles()

const componentReady = ref(false)

const { form } = useForm()

const modalTitle = computed(() => t('core::role.edit') + ' ' + form.name)

function update() {
  form.put(`/roles/${route.params.id}`).then(role => {
    setRole(role.id, role)
    Innoclapps.success(t('core::role.updated'))
    router.back()
  })
}

async function prepareComponent(id) {
  const role = await fetchRole(id)

  form.set({
    ...role,
    ...{
      permissions: role.permissions.map(permission => permission.name),
    },
  })

  nextTick(() => (componentReady.value = true))
}

prepareComponent(route.params.id)
</script>
