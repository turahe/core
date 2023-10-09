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
import IFormGroupComponent from './IFormGroup.vue'
import IFormLabelComponent from './IFormLabel.vue'
import IFormCheckboxComponent from './IFormCheckbox.vue'
import IFormToggleComponent from './IFormToggle.vue'
import IFormRadioComponent from './IFormRadio.vue'
import IFormErrorComponent from './IFormError.vue'
import IFormTextComponent from './IFormText.vue'
import IFormInputComponent from './IFormInput.vue'
import IFormInputDropdownComponent from './IFormInputDropdown.vue'
import IFormNumericInputComponent from './IFormNumericInput.vue'
import IFormSelectComponent from './IFormSelect.vue'
import IFormTextareaComponent from './IFormTextarea.vue'

const IFormPlugin = {
  install(app) {
    app.component('IFormGroup', IFormGroupComponent)
    app.component('IFormLabel', IFormLabelComponent)
    app.component('IFormError', IFormErrorComponent)
    app.component('IFormText', IFormTextComponent)
    app.component('IFormInput', IFormInputComponent)
    app.component('IFormNumericInput', IFormNumericInputComponent)
    app.component('IFormSelect', IFormSelectComponent)
    app.component('IFormTextarea', IFormTextareaComponent)
    app.component('IFormCheckbox', IFormCheckboxComponent)
    app.component('IFormRadio', IFormRadioComponent)
    app.component('IFormToggle', IFormToggleComponent)
    app.component('IFormInputDropdown', IFormInputDropdownComponent)
  },
}

// Components
export const IFormGroup = IFormGroupComponent
export const IFormLabel = IFormLabelComponent
export const IFormCheckbox = IFormCheckboxComponent
export const IFormToggle = IFormToggleComponent
export const IFormRadio = IFormRadioComponent
export const IFormError = IFormErrorComponent
export const IFormText = IFormTextComponent
export const IFormInput = IFormInputComponent
export const IFormInputDropdown = IFormInputDropdownComponent
export const IFormNumericInput = IFormNumericInputComponent
export const IFormSelect = IFormSelectComponent
export const IFormTextarea = IFormTextareaComponent

// Plugin
export default IFormPlugin
