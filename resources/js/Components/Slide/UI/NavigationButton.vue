<script setup lang="ts">
  withDefaults(
    defineProps<{
      type?: 'button' | 'submit' | 'reset'
      theme?: 'primary' | 'secondary' | 'neutral'
      outline?: boolean
    }>(),
    {
      type: 'button',
      theme: 'primary',
      outline: false,
    }
  )
</script>

<template>
  <button :type="type" class="btn btn-sm" :class="`btn-${theme} ${outline ? 'btn-outline' : ''}`">
    <slot />
  </button>
</template>
