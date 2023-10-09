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
const DefaultCollection = {
  data: [],
  current_page: 1,
  per_page: 25,
  per_page_options: [25, 50, 100],
  total: 0,
  last_page: 1,
  from: 1,
  to: 1,
}

class Paginator {
  /**
   * Class Constructor
   * @param state @type {{}|DefaultCollection}
   */
  constructor(state = {}) {
    this.state = Object.assign({}, DefaultCollection, state)
  }

  /**
   * (Getter) pagination Links
   * @return {Array}
   */
  get pagination() {
    return this.buildLinks(this.currentPage, this.lastPage)
  }

  /**
   * (Getter) items
   * @return {Array}
   */
  get items() {
    return this.state.data
  }

  /**
   * (Getter) hasItems
   * @return {Boolean}
   */
  get hasItems() {
    return this.items.length > 0
  }

  /**
   * (Getter) currentPage
   * @return {Number}
   */
  get currentPage() {
    return this.getPaginationAttribute('current_page')
  }

  /**
   * (Setter) currentPage
   * @param value @type {Number}
   * @return void
   */
  set currentPage(value) {
    this.setPaginationAttribute('current_page', value)
  }

  /**
   * (Getter) from
   * @return {Number}
   */
  get from() {
    return this.getPaginationAttribute('from')
  }

  /**
   * (Getter) to
   * @return {Number}
   */
  get to() {
    return this.getPaginationAttribute('to')
  }

  /**
   * (Getter) lastPage
   * @return {Number}
   */
  get lastPage() {
    return this.getPaginationAttribute('last_page')
  }

  /**
   * (Getter) perPage
   * @return {Number}
   */
  get perPage() {
    return this.getPaginationAttribute('per_page')
  }

  /**
   * (Setter) perPage
   * @param value @type {Number}
   * @return void
   */
  set perPage(value) {
    this.setPaginationAttribute('per_page', value)
    this.currentPage = 1
  }

  /**
   * (Getter) total
   * @return {Number}
   */
  get total() {
    return this.getPaginationAttribute('total')
  }

  /**
   * (Getter) perPageOptions
   * @return {Number}
   */
  get perPageOptions() {
    let perPageOptions = this.state.per_page_options
    let perPage = Number(this.perPage)

    if (perPageOptions.includes(perPage)) {
      return perPageOptions
    }

    perPageOptions.push(perPage)
    perPageOptions.sort(function (a, b) {
      return a - b
    })

    return perPageOptions
  }

  /**
   * (Setter) perPageOptions
   * @return {Array}
   */
  set perPageOptions(value) {
    return (this.state.per_page_options = value)
  }

  /**
   * (Getter) hasPagination
   * @return {Boolean}
   */
  get hasPagination() {
    return this.lastPage > 1
  }

  /**
   * (Getter) shouldRenderLinks
   * @return {Boolean}
   */
  get shouldRenderLinks() {
    return this.pagination.includes(this.currentPage)
  }

  /**
   * (Getter) hasNextPage
   * @return {Boolean}
   */
  get hasNextPage() {
    return this.currentPage + 1 <= this.lastPage
  }

  /**
   * (Getter) hasPreviousPage
   * @return {Boolean}
   */
  get hasPreviousPage() {
    return this.currentPage - 1 >= 1
  }

  /**
   * (Action) toPreviousPage
   * @return void
   */
  previousPage() {
    this.page(this.currentPage - 1)
  }

  /**
   * (Action) toNextPage
   * @return void
   */
  nextPage() {
    this.page(this.currentPage + 1)
  }

  /**
   * (Action) page
   * @param value @type {Number}
   * @return void
   */
  page(value) {
    this.currentPage = value
  }

  /**
   * (Conditional Method) isCurrentPage
   * @param value @type {Number}
   * @return {Boolean}
   */
  isCurrentPage(value) {
    return this.currentPage === value
  }

  /**
   * (Internal) buildLinks
   * @param currentPage @type {Number}
   * @param pageCount @type {Number}
   * @param delta @type {Number}
   * @return {Array}
   */
  buildLinks(currentPage, pageCount, delta = 3) {
    let range = []

    for (
      let i = Math.max(2, currentPage - delta);
      i <= Math.min(pageCount - 1, currentPage + delta);
      i++
    ) {
      range.push(i)
    }

    if (currentPage - delta > 2) {
      range.unshift('...')
    }

    if (currentPage + delta < pageCount - 1) {
      range.push('...')
    }

    range.unshift(1)
    range.push(pageCount)

    return range
  }

  /**
   * (Method) Get attribute from the pagination
   * @param attribute @type {String}
   * @return {*}
   */
  getPaginationAttribute(attribute) {
    return this.state[attribute]
  }

  /**
   * (Method) Set Field
   * @param attribute @type {String}
   * @param value @type {*}
   * @return {this}
   */
  setPaginationAttribute(attribute, value) {
    if (this.state[attribute]) {
      this.state[attribute] = value
    }

    return this
  }

  /**
   * (Method) State
   * @param state @type {{}}
   * @return void
   */
  setState(state) {
    this.state = Object.assign({}, this.state, state)
  }

  /**
   * (Method) flush State
   * @return void
   */
  flush() {
    this.state = DefaultCollection
  }
}

export default Paginator
