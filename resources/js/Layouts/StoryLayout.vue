<script lang="ts" setup>
  import FlashProvider from '@/Components/Flash/FlashProvider.vue'
  import PageFooter from '@/Components/PageFooter.vue'
  import PageHeader from '@/Components/PageHeader.vue'

  defineProps<{
    title?: string
    published?: boolean
  }>()
</script>
<template>
  <FlashProvider>
    <div class="min-h-screen bg-base-200 flex flex-col">
      <!-- Page Header -->
      <PageHeader :title="title">
        <slot name="top" />
      </PageHeader>
      <!-- Page Heading -->
      <header v-if="$slots.header" class="shadow-lg bg-base-100 z-50">
        <div class="p-4 space-y-2">
          <div class="flex flex-col items-center justify-center gap-3">
            <slot name="pre-header" />
            <slot name="header" />
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="stretched">
        <slot />
      </main>

      <!-- Page Footer -->
      <PageFooter>
        <slot name="bottom" />
      </PageFooter>
    </div>
  </FlashProvider>
</template>
