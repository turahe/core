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
  <div>
    <label
      class="text-sm font-medium text-neutral-700 dark:text-neutral-100"
      v-t="'core::app.visibility_group.visible_to'"
    />
    <fieldset class="mt-2">
      <legend class="sr-only">Visibility group</legend>
      <div class="space-y-3 sm:flex sm:items-center sm:space-x-4 sm:space-y-0">
        <IFormRadio
          :modelValue="type"
          @update:modelValue="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
          :disabled="disabled"
          value="all"
        >
          {{ $t('core::app.visibility_group.all') }}
        </IFormRadio>
        <IFormRadio
          :modelValue="type"
          @update:modelValue="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
          :disabled="disabled"
          value="teams"
        >
          {{ $t('users::team.teams') }}
        </IFormRadio>
        <IFormRadio
          :modelValue="type"
          @update:modelValue="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
          :disabled="disabled"
          value="users"
        >
          {{ $t('users::user.users') }}
        </IFormRadio>
      </div>
    </fieldset>

    <div v-show="type === 'users'" class="mt-4">
      <ICustomSelect
        :modelValue="dependsOn"
        @update:modelValue="$emit('update:dependsOn', $event)"
        :options="usersWithoutAdministrators"
        :placeholder="$t('users::user.select')"
        label="name"
        multiple
        :reduce="option => option.id"
      />
      <span
        class="mt-0.5 block text-right text-xs text-neutral-500 dark:text-neutral-300"
        v-t="'users::user.admin_users_excluded'"
      />
    </div>
    <div v-show="type === 'organization'" class="mt-4">
      <ICustomSelect
        :modelValue="dependsOn"
        @update:modelValue="$emit('update:dependsOn', $event)"
        :options="teams"
        :placeholder="$t('users::team.select')"
        label="name"
        multiple
        :reduce="option => option.id"
      />
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { useTeams } from '~/Users/resources/js/composables/useTeams'
import { useApp } from '~/Core/resources/js/composables/useApp'

const emit = defineEmits(['update:type', 'update:dependsOn'])

const props = defineProps({ disabled: Boolean, dependsOn: Array, type: String })

const { users } = useApp()

const { teamsByName: teams } = useTeams()

const usersWithoutAdministrators = computed(() =>
  users.value.filter(user => !user.super_admin)
)
</script>
