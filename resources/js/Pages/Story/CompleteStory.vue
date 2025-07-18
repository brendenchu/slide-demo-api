<script lang="ts" setup>
  import { Head, Link } from '@inertiajs/vue3'
  import StoryLayout from '@/Layouts/StoryLayout.vue'
  import { Project, ProjectStep } from '@/types'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import { ProgressBar, ProgressTimeline } from '@/Components/Story/v1/UI'
  import axios from 'axios'

  defineProps<{
    project: Project
    step: ProjectStep
    token: string
    allSteps: Record<string, string>
  }>()

  const newForm = () => {
    window.location.href = route('story.create')
  }

  function logoutUser() {
    axios.post(route('logout')).then(() => {
      window.location.href = route('home')
    })
  }
</script>

<template>
  <Head title="Form Complete!" />
  <StoryLayout>
    <template #top>
      <div class="flex justify-between items-start gap-2">
        <ProgressTimeline
          v-once
          :project="project"
          :step="step"
          :token="token"
          class="hidden lg:flex lg:justify-center"
        />
      </div>
    </template>
    <ProgressBar :step="step" class="lg:hidden" />
    <section class="stretched">
      <div class="stretched contained centered">
        <div class="prose prose-2xl">
          <h2>Form Complete!</h2>
          <p>Congratulations! You have completed the Slide Form Demo.</p>
          <p>From here, you have three options:</p>
          <ol>
            <li>
              <p>You can revisit any of the sections.</p>
              <ul class="prose-sm">
                <li v-for="(name, slug) in allSteps" :key="slug">
                  <Link :href="route('story.form', { project: project.id, step: slug, token })" class="hover:font-bold">
                    {{ name }}
                  </Link>
                </li>
              </ul>
            </li>
            <li>
              <p>You can fill out the form again, though I don't know why you would want to do that.</p>
              <PrimaryButton class="lg:btn-lg xl:btn-xl btn-outline" @click="newForm"> Start New Form </PrimaryButton>
            </li>
            <li>
              <p>You can log out and have a great day!</p>
              <PrimaryButton class="lg:btn-lg xl:btn-xl btn-outline" @click="logoutUser"> Log Out </PrimaryButton>
            </li>
          </ol>
        </div>
      </div>
    </section>
  </StoryLayout>
</template>
