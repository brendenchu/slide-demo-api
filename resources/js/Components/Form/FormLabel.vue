<script lang="ts" setup>
  defineProps<{
    value?: string
  }>()
</script>

<template>
  <label class="label">
    <span v-if="value">{{ value }}</span>
    <span v-else><slot /></span>
  </label>
</template>

<style scoped>
  .label {
    @apply p-1;
  }

  .label > span {
    @apply flex flex-col gap-2;
  }
</style>
