<script lang="ts" setup>
  import { Head, useForm } from '@inertiajs/vue3'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import StoryLayout from '@/Layouts/StoryLayout.vue'

  const form = useForm({})

  const createForm = () => {
    form.post(route('story.store'))
  }
</script>

<template>
  <Head title="New Form" />
  <StoryLayout>
    <section class="stretched">
      <div class="stretched contained centered">
        <div class="prose prose-2xl pb-4">
          <h2>Start New Form</h2>
          <p>Welcome to the Slide Form Demo. You will be guided through a multi-step form divided into sections.</p>
          <p>To get started, click the button below.</p>
          <PrimaryButton class="lg:btn-lg xl:btn-xl btn-outline" @click="createForm"> Let's Begin </PrimaryButton>
        </div>
      </div>
    </section>
  </StoryLayout>
</template>
