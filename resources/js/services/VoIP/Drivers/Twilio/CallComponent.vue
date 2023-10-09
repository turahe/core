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
  <IModal
    id="callingActivation"
    cancel-title="Activate"
    cancel-variant="success"
    :title="$t('calls::call.activation_required')"
    @hidden="callingModalHiddenEvent"
  >
    <!-- Removes the ok button by providing an empty slot -->
    <template #modal-ok>&nbsp;</template>
    <p class="text-neutral-800 dark:text-white">
      {{
        $t('calls::call.activation_gesture_required', {
          askForActivationIn: askForActivationIn,
        })
      }}
    </p>
  </IModal>
  <div
    v-show="showCallComponent"
    class="sticky top-0 border-b border-neutral-300 bg-white/80 px-7 py-3 dark:border-neutral-700 dark:bg-neutral-800/80"
  >
    <IAlert
      class="mb-4"
      :show="error !== null"
      variant="danger"
      dismissible
      @dismissed="error = null"
    >
      {{ error }}
    </IAlert>

    <div class="flex items-center">
      <div class="grow">
        <div class="flex items-center space-x-4">
          <p
            class="text-base font-medium"
            :class="
              isCallInProgress || (isIncoming && !isCallInProgress)
                ? 'text-success-500'
                : 'text-neutral-800 dark:text-neutral-100'
            "
            v-text="cardHeader"
          />
          <IBadge
            v-show="isCallInProgress || (lastConnectedNumber && duration)"
            variant="info"
          >
            <Icon icon="Clock" class="mr-1 h-4 w-4 text-current" />
            {{ isCallInProgress || lastConnectedNumber ? duration : null }}
          </IBadge>
          <div>
            <div class="ml-0 mt-2 space-x-2 sm:ml-2 sm:mt-0">
              <!-- Somehow the tooltip is not reactive for language changes
          e.q. v-i-tooltip="isMuted ? $t('unmuted'): $t('mute')"
          for this reason, we will use 2 buttons -->
              <IButton
                v-if="isCallInProgress"
                v-show="isMuted"
                :rounded="false"
                :size="false"
                class="h-9 w-9 justify-center rounded-full"
                variant="white"
                @click="$options.call.mute(false)"
                icon="VolumeUp"
                v-i-tooltip="$t('calls::call.unmute')"
              />
              <IButton
                v-if="isCallInProgress"
                v-show="!isMuted"
                :rounded="false"
                :size="false"
                class="h-9 w-9 justify-center rounded-full"
                variant="white"
                @click="$options.call.mute(true)"
                icon="VolumeOff"
                v-i-tooltip="$t('calls::call.mute')"
              />
              <IButton
                v-show="isIncoming && !isCallInProgress"
                :rounded="false"
                :size="false"
                class="h-9 w-9 justify-center rounded-full"
                @click="$options.call.accept()"
                v-i-tooltip="$t('calls::call.answer')"
                variant="success"
                icon="Phone"
              />
              <IButton
                v-show="isCallInProgress"
                :rounded="false"
                :size="false"
                class="h-9 w-9 justify-center rounded-full"
                @click="$options.call.disconnect()"
                v-i-tooltip="$t('calls::call.hangup')"
                variant="danger"
                icon="X"
              />
              <IButton
                v-show="isIncoming && !isCallInProgress"
                :rounded="false"
                :size="false"
                class="h-9 w-9 justify-center rounded-full"
                @click="$options.call.reject()"
                icon="PhoneXMark"
                v-i-tooltip="$t('calls::call.reject')"
                variant="danger"
              />
            </div>
          </div>
        </div>
      </div>
      <div>
        <IButtonIcon
          icon="X"
          class="mt-2"
          @click="hideCallHandler"
          v-show="visible === true"
          v-i-tooltip.left="$t('calls::call.hide_bar')"
        />
      </div>
    </div>
    <div class="mt-4 flex flex-col items-center md:flex-row">
      <div
        class="flex flex-col items-center space-y-2 sm:flex-row md:space-x-2 md:space-y-0"
      >
        <DropdownSelectInput
          v-model="audioDevices.speaker.selected"
          :items="audioDevices.speaker.items"
          placement="bottom-start"
          @change="getDevice().instance.audio.speakerDevices.set([$event.id])"
        >
          <template #label="{ label }">
            <div class="flex items-center">
              <Icon icon="Speakerphone" class="mr-1 h-5 w-5" />
              {{ strTruncate(label ? label : '', 25) }}
            </div>
          </template>
        </DropdownSelectInput>
        <DropdownSelectInput
          v-model="audioDevices.ringtone.selected"
          placement="bottom-start"
          @change="getDevice().instance.audio.ringtoneDevices.set([$event.id])"
          :items="audioDevices.ringtone.items"
        >
          <template #label="{ label }">
            <div class="flex items-center">
              <Icon icon="Microphone" class="mr-1 h-5 w-5" />
              {{ strTruncate(label ? label : '', 25) }}
            </div>
          </template>
        </DropdownSelectInput>
        <a
          href="#"
          class="link hidden text-sm lg:block"
          @click.prevent="getMediaDevices"
          v-t="'calls::call.unknown_devices'"
        />
      </div>
      <div
        v-show="isCallInProgress"
        class="ml-5 flex space-x-3 pl-5 sm:border-l sm:border-neutral-300"
      >
        <div class="flex items-center">
          <Icon
            icon="VolumeUp"
            class="h-5 w-5 text-current dark:text-neutral-200"
            v-i-tooltip.bottom="$t('calls::call.speaker_volume')"
          />
          <div class="ml-1 h-4 rounded-md" ref="outputVolumeBar"></div>
        </div>
        <div class="flex items-center">
          <Icon
            icon="Microphone"
            class="h-4 w-5 text-current dark:text-neutral-200"
            v-i-tooltip.bottom="$t('calls::call.mic_volume')"
          />
          <div class="ml-1 h-4 rounded-md" ref="inputVolumeBar"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { strTruncate } from '@/utils'
import throttle from 'lodash/throttle'
export default {
  data: () => ({
    error: null,
    isCallInProgress: false,
    callStartedDate: null,
    callEndedDate: null,
    isIncoming: false,
    duration: null,
    durationInterval: null,
    visible: false,
    isMuted: false,
    lastConnectedNumber: null,
    personDisplayName: null,
    // Minutes
    askForActivationIn: 1,
    audioDevices: {
      speaker: {
        items: [],
        selected: null,
      },
      ringtone: {
        items: [],
        selected: null,
      },
    },
  }),
  watch: {
    lastConnectedNumber: function (newVal) {
      if (newVal) {
        this.findDisplayNameForCall(newVal)
      }
    },
  },
  computed: {
    /**
     * Indicates whether the call component should be shown
     *
     * @return {Boolean}
     */
    showCallComponent() {
      return this.visible === true || this.isCallInProgress
    },

    /**
     * Get the card headerr
     *
     * @return {String}
     */
    cardHeader() {
      // Incoming call
      if (this.isIncoming && !this.isCallInProgress) {
        return this.$t('calls::call.new_from', {
          number: this.personDisplayName || this.lastConnectedNumber,
        })
      }
      // Already connected call
      else if (this.isCallInProgress) {
        return this.$t('calls::call.connected_with', {
          number: this.personDisplayName || this.lastConnectedNumber,
        })
      }
      // Ended call, shows the last connected number
      else if (this.lastConnectedNumber) {
        return this.$t('calls::call.ended', {
          number: this.personDisplayName || this.lastConnectedNumber,
        })
      }

      return ''
    },
  },
  methods: {
    strTruncate,

    /**
     * Find the display name for the call
     *
     * @param {String} phoneNumber
     *
     * @return {Void}
     */
    findDisplayNameForCall(phoneNumber) {
      let queryString = {
        search_fields: 'phones.number:=',
        q: phoneNumber,
      }

      Promise.all([
        Innoclapps.request().get('/contacts/search', { params: queryString }),
        Innoclapps.request().get('/companies/search', { params: queryString }),
      ]).then(values => {
        let contacts = values[0].data
        let companies = values[1].data

        if (contacts.length > 0) {
          this.personDisplayName = contacts[0].display_name
        } else if (companies.length > 0) {
          this.personDisplayName = companies[0].display_name
        } else {
          this.personDisplayName = null
        }
      })
    },

    /**
     * Manually hide the call handler cards
     *
     * @return {Void}
     */
    hideCallHandler() {
      this.visible = false
      // Clear the duration so the next time call is connected
      // to not see the previous durection during the call initialization
      this.duration = null
    },

    /**
     * Handle the calling modal hidden event
     *
     * @return {Void}
     */
    callingModalHiddenEvent() {
      Innoclapps.success('Activated')
    },

    /**
     * Get the duration of the current call
     *
     * @return {Void}
     */
    updateDuration() {
      let endCallDate = this.callEndedDate || moment()
      let duration = moment.duration(endCallDate.diff(this.callStartedDate))

      let minutes = duration.minutes()
      let seconds = duration.seconds()

      this.duration =
        (minutes < 10 ? '0' + minutes : minutes) +
        ':' +
        (seconds < 10 ? '0' + seconds : seconds)
    },
    /**
     * Prepare the call component
     *
     * @return {Void}
     */
    prepareComponent(device) {
      try {
        device.on('Registering', device => {
          // console.log('Registering')
        })

        device.on('Registered', device => {
          // console.log('Registered')
          device.instance.audio.on('deviceChange', this.updateAllDevices)
        })

        device.on('Error', ({ error, Call }) => (this.error = error.message))

        this.setDevice(device)

        this.$voip.onCall(({ Call, isIncoming }) => {
          this.callEndedDate = null
          this.callStartedDate = null
          this.duration = null
          this.$options.call = Call
          this.isIncoming = isIncoming
          this.visible = true

          if (isIncoming) {
            this.lastConnectedNumber = Call.instance.parameters.From
          } else {
            this.lastConnectedNumber = Call.instance.customParameters.get('To')
          }

          Call.on('Mute', ({ isMuted, Call }) => (this.isMuted = isMuted))

          Call.on('Error', error => {
            this.visible = true
            this.call = null
            this.isIncoming = false
            this.error = error.message
          })

          Call.on('Accept', Call => {
            // console.log('Accept')
            this.isCallInProgress = true
            this.callStartedDate = moment()

            this.bindVolumeIndicators(Call.instance)
            this.durationInterval = setInterval(this.updateDuration, 1000)
          })

          Call.on('Cancel', () => {
            // console.log('Cancel')
            this.isCallInProgress = false
            this.visibility = true
            this.isIncoming = false
            this.$options.call = null
          })

          Call.on('Reject', () => {
            // console.log('Reject')
            this.duration = null
            this.callStartedDate = null
            this.isIncoming = false
            this.isCallInProgress = false
            this.$options.call = null
          })

          Call.on('Disconnect', Call => {
            // console.log('Disconnect')
            // When disconnected, set visibility to true so the user can see
            // the call data, then he can decide whether to close the call handler bar or not
            this.visible = true
            this.isCallInProgress = false
            this.$options.call = null
            this.isMuted = false
            this.isIncoming = false
            this.callEndedDate = moment()
            clearInterval(this.durationInterval)
          })
        })
      } catch (error) {
        // Catch not supported error and any other critical errors
        // twilio.js wasn't able to find WebRTC browser support. This is most likely because this page is served over http rather than https, which does not support WebRTC in many browsers. Please load this page over https and try again.
        this.error = error.message
        this.visible = true
      }
    },

    /**
     * Set the VoIP device
     */
    setDevice(device) {
      this.$options.device = device
    },

    /**
     * Get the VoIP device
     */
    getDevice() {
      return this.$options.device
    },

    /**
     * Boot the VoIP device (on user gesture)
     *
     * @return {Void}
     */
    bootVoIPDevice() {
      if (this.getDevice()) {
        document.documentElement.removeEventListener(
          'mousedown',
          this.bootVoIPDevice
        )
        return
      }

      this.$voip.ready(this.prepareComponent)
      this.$voip.ready(this.getMediaDevices)
      this.$voip.ready(device => device.instance.register())
      this.$voip.connect()
    },

    /**
     * Update all devices
     *
     * @return {Void}
     */
    updateAllDevices() {
      this.updateDevices(
        this.getDevice().instance.audio.speakerDevices.get(),
        'speaker'
      )
      this.updateDevices(
        this.getDevice().instance.audio.ringtoneDevices.get(),
        'ringtone'
      )
    },

    /**
     * Update the available audio devices
     *
     * @param  {Array} selected Selected devices
     * @param  {String} type
     *
     * @return {Void}
     */
    updateDevices(selected, type) {
      this.audioDevices[type].items = []
      this.audioDevices[type].selected = null

      let available = this.getDevice().instance.audio.availableOutputDevices

      available.forEach((device, id) => {
        let isActive = selected.size === 0 && id === 'default'

        selected.forEach(function (device) {
          if (device.deviceId === id) {
            isActive = true
          }
        })

        let item = {
          label: device.label,
          id: id,
        }

        this.audioDevices[type].items.push(item)

        if (isActive) {
          this.audioDevices[type].selected = item
        }
      })
    },

    /**
     * Get the available audio devices from navigator
     *
     * @return {Array}
     */
    getMediaDevices() {
      // https://stackoverflow.com/questions/52479734/domexception-requested-device-not-found-getusermedia
      navigator.mediaDevices
        .getUserMedia({
          audio: true,
        })
        .then(this.updateAllDevices)
        .catch(error => (this.error = error))
    },

    /**
     * Bind the volume indicators
     *
     * @param  {Object} Twilio.Call
     *
     * @return {Void}
     */
    bindVolumeIndicators(TwilioCall) {
      TwilioCall.on('volume', (inputVolume, outputVolume) => {
        let inputColor = 'red'
        if (inputVolume < 0.5) {
          inputColor = 'green'
        } else if (inputVolume < 0.75) {
          inputColor = 'yellow'
        }

        this.$refs.inputVolumeBar.style.width =
          Math.floor(inputVolume * 300) + 'px'
        this.$refs.inputVolumeBar.style.background = inputColor

        let outputColor = 'red'
        if (outputVolume < 0.5) {
          outputColor = 'green'
        } else if (outputVolume < 0.75) {
          outputColor = 'yellow'
        }

        this.$refs.outputVolumeBar.style.width =
          Math.floor(outputVolume * 300) + 'px'
        this.$refs.outputVolumeBar.style.background = outputColor
      })
    },
  },
  created() {
    document.documentElement.addEventListener(
      'mousedown',
      throttle(this.bootVoIPDevice, 500)
    )

    window.addEventListener('beforeunload', () => {
      this.getDevice() && this.getDevice().unregister()
    })

    // If the user did not clicked anything in 1 minute
    // We will show a modal to activate the calling functionality
    // As most browsers requires user gesture to enable audio/mic
    setTimeout(() => {
      if (!this.getDevice()) {
        this.$iModal.show('callingActivation')
      }
    }, this.askForActivationIn * 60 * 1000)
  },
}
</script>
