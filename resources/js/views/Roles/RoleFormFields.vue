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
  <IFormGroup label-for="name" :label="$t('core::role.name')" required>
    <IFormInput
      v-model="form.name"
      id="name"
      ref="nameRef"
      name="name"
      type="text"
    >
    </IFormInput>
    <IFormError v-text="form.getError('name')" />
  </IFormGroup>
  <IOverlay :show="isLoading" class="mt-5">
    <div v-show="permissions.all.length > 0">
      <h3
        class="my-4 whitespace-nowrap text-lg/6 font-medium text-neutral-800 dark:text-neutral-200"
        v-t="'core::role.permissions'"
      />

      <div
        v-for="(group, index) in permissions.grouped"
        :key="index"
        class="mb-4"
      >
        <p
          class="mb-1 font-medium text-neutral-700 dark:text-neutral-200"
          v-text="group.as"
        />

        <div v-for="view in group.views" :key="view.group">
          <div class="flex justify-between">
            <p class="text-sm text-neutral-600 dark:text-neutral-300">
              {{
                view.as
                  ? view.as
                  : view.single
                  ? view.permissions[view.keys[0]]
                  : ''
              }}
            </p>

            <IDropdown placement="bottom-end">
              <template #toggle="{ toggle }">
                <button
                  type="button"
                  @click="toggle"
                  class="link inline-flex items-center text-sm"
                >
                  {{ getSelectedPermissionTextByView(view) }}
                  <Icon icon="ChevronDown" class="ml-1 h-5 w-5" />
                </button>
              </template>

              <div class="py-1">
                <IDropdownItem
                  @click="revokePermission(view)"
                  v-show="view.revokeable"
                  :text="$t('core::role.revoked')"
                />
                <IDropdownItem
                  v-if="view.single"
                  @click="setSelectedPermission(view, view.keys[0])"
                  :text="$t('core::role.granted')"
                />
                <IDropdownItem
                  v-else
                  v-for="(permission, key) in view.permissions"
                  :key="key"
                  :disabled="selectedPermissions.indexOf(key) > -1"
                  @click="setSelectedPermission(view, key)"
                  :text="permission"
                />
              </div>
            </IDropdown>
          </div>
        </div>
      </div>
    </div>
  </IOverlay>
</template>
<script setup>
import { ref, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import { useLoader } from '~/Core/resources/js/composables/useLoader'

const props = defineProps({
  // Whether the form is embedded in create view
  create: { type: Boolean, default: false },
  form: { required: true, type: Object, default: () => ({}) },
})

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()

const selectedPermissions = ref([])
const permissions = ref({ all: [], grouped: {} })
const nameRef = ref(null)

function getSelectedPermissionTextByView(view) {
  if (view.single) {
    if (selectedPermissions.value.indexOf(view.keys[0]) > -1) {
      return t('core::role.granted')
    }
    return t('core::role.revoked')
  }

  for (let permission in view.keys) {
    if (selectedPermissions.value.indexOf(view.keys[permission]) > -1) {
      return view.permissions[view.keys[permission]]
    }
  }

  return t('core::role.revoked')
}

function setSelectedPermission(view, permissionKey) {
  // Revoke any previously view permissions
  revokePermission(view)
  // Now set the new selected permission
  selectedPermissions.value.push(permissionKey)
  // Update the form permissions with the new one
  nextTick(() => {
    props.form.permissions = selectedPermissions.value
  })
}

function revokePermission(view) {
  for (let permission in view.keys) {
    let index = selectedPermissions.value.indexOf(view.keys[permission])

    if (index != -1) {
      selectedPermissions.value.splice(index, 1)
    }
  }
}

function setDefaultSelectedPermissions(permissions) {
  for (let group in permissions.grouped) {
    permissions.grouped[group].views.forEach(view => {
      // When creating new role set the first permission as selected
      // This is applied if there are more then one available child permission for a view
      if (!view.single) {
        setSelectedPermission(view, Object.keys(view.permissions)[0])
      }
    })
  }
}

function fetchAndSetPermissions() {
  setLoading(true)

  Innoclapps.request()
    .get('/permissions')
    .then(({ data }) => {
      permissions.value = data

      if (!props.create) {
        selectedPermissions.value = props.form.permissions

        return
      }

      setDefaultSelectedPermissions(data)
    })
    .finally(() => setLoading(false))
}

fetchAndSetPermissions()

defineExpose({ nameRef })
</script>
