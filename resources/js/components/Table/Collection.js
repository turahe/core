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
/**
 * Default Collection Attributes.
 * @const DefaultCollection
 * @example {
 * order: array,
 * }
 */
const DefaultCollection = {
  order: [
    {
      attribute: 'id',
      direction: 'desc',
    },
  ],
}

import Paginator from '~/Core/resources/js/services/ResourcePaginator'
/**
 * Collection Class
 * Laravel Paginator Compatible Schema
 */
class Collection extends Paginator {
  /**
   * Class Constructor
   * @param state {Object|DefaultCollection}
   */
  constructor(state = {}) {
    super(Object.assign({}, DefaultCollection, state))
  }

  /**
   * (Method) Append Object
   * @param obj {Object}
   * @param dupes {Boolean}
   * @return {this}
   */
  append(obj, dupes = false) {
    if (!this.first(obj) || dupes) this.state.data.push(obj)
    this.state.total++
    return this
  }

  /**
   * (Method) Prepend Object
   * @param obj {Object}
   * @param dupes {Boolean}
   * @return {this}
   */
  prepend(obj, dupes = false) {
    if (!this.first(obj) || dupes) this.state.data.unshift(obj)
    this.state.total++
    return this
  }

  /**
   * (Method) Find Object by Property
   * @param obj {{*}}
   * @return {Boolean}
   */
  has(obj) {
    return this.state.data.indexOf(obj) >= 0
  }

  /**
   * (Method) Find Many Objects by Property
   * @param obj {Number|{}}
   * @param prop {Number|String}
   * @return {*}
   */
  find(obj, prop = 'id') {
    return this.state.data.filter(entry => obj[prop] === entry[prop])
  }

  /**
   * (Method) Find Object by Property
   * @param obj {Number|{}}
   * @param prop {Number|String}
   * @return {*}
   */
  first(obj, prop = 'id') {
    return obj ? this.find(obj, prop)[0] : this.items[0] ? this.items[0] : null
  }

  /**
   * (Method) Update Object
   * @param obj {Object}
   * @param prop {String}
   * @return {this}
   */
  update(obj, prop = 'id') {
    const entries = this.find(obj, prop)
    if (entries) {
      entries.forEach(entry =>
        this.replace(entry, Object.assign({}, entry, obj))
      )
    }
    return this
  }

  /**
   * (Method) Remove Object
   * @param obj {Object}
   * @param prop {String}
   * @return {this}
   */
  remove(obj, prop = 'id') {
    const entries = this.find(obj, prop)
    if (entries) {
      entries.forEach(entry => {
        this.state.data.splice(this.state.data.indexOf(entry), 1)
        this.state.total--
      })
    }
    return this
  }

  /**
   * (Method) Replace Object
   * @param entry {{*}}
   * @param obj {{*}}
   * @return {this}
   */
  replace(entry, obj) {
    this.state.data.splice(this.state.data.indexOf(entry), 1, obj)
    return this
  }

  /**
   * (Action) toggleSortable
   *
   * @param {String} attribute
   *
   * @return void
   */
  toggleSortable(attribute) {
    if (this.isOrderedBy(attribute)) {
      this.set('order', [
        {
          attribute: attribute,
          direction: this.isSorted('desc', attribute) ? 'asc' : 'desc',
        },
      ])

      return
    }

    this.set('order', [
      {
        attribute: attribute,
        direction: 'desc',
      },
    ])
  }

  /**
   * (Conditional Method) isOrderedBy
   *
   * @param {String} attribute
   *
   * @return {Boolean}
   */
  isOrderedBy(attribute) {
    const order = this.get('order')

    return Boolean(order.filter(object => object.attribute == attribute).length)
  }

  /**
   * (Conditional Method) isSorted
   *
   * @param direction {String}
   * @param attribute {String}
   *
   * @return {Boolean}
   */
  isSorted(direction, attribute) {
    return Boolean(
      this.get('order').filter(
        object =>
          object.attribute === attribute && object.direction == direction
      ).length
    )
  }

  /**
   * (Getter) urlParams
   *
   * @return {Object}
   */
  get urlParams() {
    return {
      per_page: this.perPage,
      page: this.currentPage,
      order: this.get('order'),
    }
  }

  /**
   * (Method) Get Field Value
   * @param field {String}
   * @return {*}
   */
  get(field) {
    return this.state[field]
  }

  /**
   * (Method) Set Field
   * @param field {String}
   * @param value {*}
   * @return {this}
   */
  set(field, value) {
    this.state[field] = value
    return this
  }
}

export default Collection
