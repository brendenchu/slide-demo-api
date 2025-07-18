<script setup lang="ts">
  import { onMounted, ref } from 'vue'

  defineProps<{
    modelValue: string | number | boolean | null
  }>()

  defineEmits(['update:modelValue'])

  const input = ref<HTMLInputElement | null>(null)

  onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
      input.value?.focus()
    }
  })

  defineExpose({ focus: () => input.value?.focus() })
</script>

<template>
  <input
    ref="input"
    class="input input-bordered input-sm"
    :value="modelValue"
    @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
  />
</template>
