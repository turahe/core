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
import { useI18n } from 'vue-i18n'

export function useQueryBuilderLabels() {
  const { t } = useI18n()

  const labels = {
    operatorLabels: {
      is: t('core::filters.operators.is'),
      was: t('core::filters.operators.was'),
      equal: t('core::filters.operators.equal'),
      not_equal: t('core::filters.operators.not_equal'),
      in: t('core::filters.operators.in'),
      not_in: t('core::filters.operators.not_in'),
      less: t('core::filters.operators.less'),
      less_or_equal: t('core::filters.operators.less_or_equal'),
      greater: t('core::filters.operators.greater'),
      greater_or_equal: t('core::filters.operators.greater_or_equal'),
      between: t('core::filters.operators.between'),
      not_between: t('core::filters.operators.not_between'),
      begins_with: t('core::filters.operators.begins_with'),
      not_begins_with: t('core::filters.operators.not_begins_with'),
      contains: t('core::filters.operators.contains'),
      not_contains: t('core::filters.operators.not_contains'),
      ends_with: t('core::filters.operators.ends_with'),
      not_ends_with: t('core::filters.operators.not_ends_with'),
      is_empty: t('core::filters.operators.is_empty'),
      is_not_empty: t('core::filters.operators.is_not_empty'),
      is_null: t('core::filters.operators.is_null'),
      is_not_null: t('core::filters.operators.is_not_null'),
    },
    matchType: t('core::filters.match_type'),
    matchTypeAll: t('core::filters.match_type_all'),
    matchTypeAny: t('core::filters.match_type_any'),
    addRule: t('core::filters.add_condition'),
    removeRule: '<span aria-hidden="true">&times;</span>',
    addGroup: t('core::filters.add_group'),
    removeGroup: '<span aria-hidden="true">&times;</span>',
  }

  return { labels }
}
