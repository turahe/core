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
  <ICard
    :header="$t('core::role.roles')"
    no-body
    :overlay="rolesAreBeingFetched"
  >
    <template #actions>
      <IButton
        v-show="hasRoles"
        icon="Plus"
        :to="{ name: 'create-role' }"
        size="sm"
        :text="$t('core::role.create')"
      />
    </template>
    <ITable v-if="hasRoles" class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" v-t="'core::app.id'" width="5%"></th>
          <th class="text-left" v-t="'core::role.name'"></th>
          <th class="text-left"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in rolesByName" :key="role.id">
          <td v-text="role.id"></td>
          <td>
            <router-link
              class="link"
              :to="{ name: 'edit-role', params: { id: role.id } }"
            >
              {{ role.name }}
            </router-link>
          </td>
          <td class="flex justify-end">
            <IMinimalDropdown>
              <IDropdownItem
                :to="{ name: 'edit-role', params: { id: role.id } }"
                :text="$t('core::app.edit')"
              />

              <IDropdownItem
                @click="destroy(role.id)"
                :text="$t('core::app.delete')"
              />
            </IMinimalDropdown>
          </td>
        </tr>
      </tbody>
    </ITable>
    <ICardBody v-else-if="!rolesAreBeingFetched">
      <IEmptyState
        :to="{ name: 'create-role' }"
        :button-text="$t('core::role.create')"
        :title="$t('core::role.empty_state.title')"
        :description="$t('core::role.empty_state.description')"
      />
    </ICardBody>
  </ICard>

  <router-view></router-view>
</template>
<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoles } from './useRoles'

const { t } = useI18n()

const { rolesByName, rolesAreBeingFetched, deleteRole } = useRoles()

const hasRoles = computed(() => rolesByName.value.length > 0)

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteRole(id)

  Innoclapps.success(t('core::role.deleted'))
}
</script>
