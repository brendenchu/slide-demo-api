<script lang="ts" setup>
  import { computed } from 'vue'

  const emit = defineEmits(['update:checked'])

  const props = defineProps<{
    checked: string | number | boolean | null
    value?: string | number | boolean | null
  }>()

  const proxyChecked = computed({
    get() {
      return props.checked
    },

    set(val) {
      emit('update:checked', val)
    },
  })
</script>

<template>
  <input
    v-model="proxyChecked"
    :value="value"
    class="radio radio-lg radio-primary focus:radio-primary m-2"
    type="radio"
  />
</template>
