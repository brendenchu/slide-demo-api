<script lang="ts" setup>
  import { computed } from 'vue'

  const emit = defineEmits(['update:checked'])

  const props = defineProps<{
    checked: string | number | boolean | null
    value?: string | number | boolean | null
  }>()

  const proxyChecked = computed({
    get() {
      return props.checked === props.value
    },

    set(val) {
      emit('update:checked', val)
    },
  })
</script>

<template>
  <input v-model="proxyChecked" :value="value" class="checkbox checkbox-lg checkbox-primary" type="checkbox" />
</template>
