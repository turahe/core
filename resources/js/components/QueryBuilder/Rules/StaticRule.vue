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
  <IBadge variant="info" :text="$t('core::filters.static_rule_indicator')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import propsDefinition from './props'

const props = defineProps(propsDefinition)
</script>
