<script setup lang="ts">
  import { Content, Controls, Frame } from './Blocks'
  import { ref } from 'vue'
  import type { Action, Direction, SlideOptions } from './types'

  const props = withDefaults(
    defineProps<{
      current?: number
      pages?: number
      actions?: SlideOptions<Action>
      direction?: Direction
    }>(),
    {
      current: 1,
      pages: 5,
      actions: undefined,
      direction: 'next',
    }
  )

  const current = ref<number>(props.current)
  const pages = ref<number>(props.pages)
  const direction = ref<Direction>(props.direction)
  const actions = ref<SlideOptions<Action>>(
    props.actions || {
      next: {
        label: 'Next',
        callback: () => {
          current.value++
          direction.value = 'next'
        },
      },
      previous: {
        label: 'Previous',
        callback: () => {
          current.value--
          direction.value = 'previous'
        },
      },
    }
  )
</script>

<template>
  <Frame>
    <Content
      v-for="i in pages"
      v-show="(props.current ?? current) === i"
      :key="`slide-content-${i}`"
      :direction="props.direction ?? direction"
    >
      <slot :name="`page-${i}`" />
    </Content>
  </Frame>
  <Controls
    v-if="props.actions"
    :current="props.current ?? current"
    :pages="pages"
    :actions="props.actions ?? actions"
  />
</template>
