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
  <IAlert class="mb-6 border border-info-200">
    {{
      $t('core::app.soft_deletes.trashed_pruning_info', { total: prune_after })
    }}
  </IAlert>

  <ResourceTable
    :url-path="`/${resourceName}/table`"
    :table-id="`${resourceName}-trashed`"
    :data-request-query-string="{ trashed: 1 }"
    :action-request-params="{ trashed: 1 }"
    :resource-name="resourceName"
  />
</template>
<script setup>
import ResourceTable from './Table.vue'

defineProps({
  resourceName: { required: true, type: String },
})

const prune_after = Innoclapps.config('soft_deletes.prune_after')
</script>
