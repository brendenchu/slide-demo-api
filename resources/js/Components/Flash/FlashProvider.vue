<script setup lang="ts">
  import Modal from './FlashModal.vue'
  import { usePage } from '@inertiajs/vue3'
  import { computed, ComputedRef, onMounted, ref } from 'vue'

  const showModal = ref(false)
  const page = usePage()

  const type: ComputedRef<string> = computed(() => {
    if (page.props.flash.success) {
      return 'success'
    } else if (page.props.flash.error) {
      return 'error'
    } else if (page.props.flash.warning) {
      return 'warning'
    } else if (page.props.flash.info) {
      return 'info'
    } else if (page.props.flash.message) {
      return 'message'
    } else {
      return ''
    }
  })

  const data: ComputedRef<{
    message?: string
    styles?: string
  }> = computed(() => {
    switch (type.value) {
      case 'success':
        return {
          message: page.props.flash.success,
          styles: 'bg-success border-success text-success-content',
        }
      case 'error':
        return {
          message: page.props.flash.error,
          styles: 'bg-error border-error text-error-content',
        }
      case 'warning':
        return {
          message: page.props.flash.warning,
          styles: 'bg-warning border-warning text-warning-content',
        }
      case 'info':
        return {
          message: page.props.flash.info,
          styles: 'bg-info border-info text-info-content',
        }
      case 'message':
        return {
          message: page.props.flash.message,
          styles: 'bg-base-100 border-base-100 text-base-content',
        }
      default:
        return {}
    }
  })

  onMounted(() => {
    if (type.value !== '') {
      showModal.value = true
      // set a timeout to close the modal after 5 seconds
      setTimeout(() => {
        showModal.value = false
      }, 5000)
    }
  })
</script>

<template>
  <Transition
    enter-active-class="ease-out duration-300"
    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
    leave-active-class="ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
  >
    <div v-if="showModal" class="absolute top-2 inset-x-0 z-[100] flex items-center justify-center">
      <Modal :show="data.message !== ''" :max-width="'lg'" :class="'border ' + data.styles" @close="showModal = false">
        {{ data.message }}
      </Modal>
    </div>
  </Transition>
  <slot> Content goes here.</slot>
</template>

<style scoped lang="postcss"></style>
