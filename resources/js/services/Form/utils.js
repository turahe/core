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
function isFile(object) {
  return object instanceof File || object instanceof FileList
}

function objectToFormData(object, formData = new FormData(), parent = null) {
  if (object === null || object === 'undefined' || object.length === 0) {
    return formData.append(parent, object)
  }

  for (const property in object) {
    if (object.hasOwnProperty(property)) {
      appendToFormData(formData, getKey(parent, property), object[property])
    }
  }

  return formData
}

function getKey(parent, property) {
  return parent ? parent + '[' + property + ']' : property
}

function appendToFormData(formData, key, value) {
  if (value instanceof Date) {
    return formData.append(key, value.toISOString())
  }

  if (value instanceof File) {
    return formData.append(key, value, value.name)
  }

  if (typeof value === 'boolean') {
    return formData.append(key, value ? '1' : '0')
  }

  if (value === null) {
    return formData.append(key, '')
  }

  if (typeof value !== 'object') {
    return formData.append(key, value)
  }

  objectToFormData(value, formData, key)
}

export { isFile, objectToFormData }
