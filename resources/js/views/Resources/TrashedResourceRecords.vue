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
    <ResourceTrashedTable :resource-name="$route.params.resourceName" />
  </ILayout>
</template>
<script setup>
import ResourceTrashedTable from '~/Core/resources/js/components/Table/TrashedTable.vue'
</script>
