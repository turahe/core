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
import routes from './routes'

import RecordStore from './store/Record'
import FieldsStore from './store/Fields'
import TableStore from './store/Table'
import FiltersStore from './store/Filters'
import RecordPreviewStore from './store/RecordPreview'
import { useTags } from './components/Tags/useTags'

const { setTags } = useTags()

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router, store) {
    store.registerModule('record', RecordStore)
    store.registerModule('fields', FieldsStore)
    store.registerModule('table', TableStore)
    store.registerModule('filters', FiltersStore)
    store.registerModule('recordPreview', RecordPreviewStore)

    setTags(Innoclapps.config('tags') || [])

    // Routes
    routes.forEach(route => router.addRoute(route))
  })
}
