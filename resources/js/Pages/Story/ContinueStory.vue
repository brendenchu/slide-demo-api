<script lang="ts" setup>
  import { Head, useForm } from '@inertiajs/vue3'
  import StoryLayout from '@/Layouts/StoryLayout.vue'
  import { Project, ProjectStep } from '@/types'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import { ProgressBar, ProgressTimeline } from '@/Components/Story/v1/UI'

  const props = defineProps<{
    project: Project
    step: ProjectStep
    position: {
      step: string
      page: number
    }
  }>()

  const form = useForm({})

  const loadForm = () => {
    form.get(
      route('story.form', {
        project: props.project,
        step: props.position.step,
        token: props.project.token,
        page: props.position.page,
      })
    )
  }
</script>

<template>
  <Head title="Continue Form" />
  <StoryLayout>
    <template #top>
      <div class="flex justify-between items-start gap-2">
        <ProgressTimeline v-once :project="project" :step="step" class="hidden lg:flex lg:justify-center" />
      </div>
    </template>
    <ProgressBar :step="step" class="lg:hidden" />
    <section class="stretched">
      <div class="stretched contained centered">
        <div class="prose prose-2xl pb-4">
          <h2>Continue Form</h2>
          <p>You have already started the Slide Form Demo. You can continue where you left off.</p>
          <PrimaryButton class="lg:btn-lg xl:btn-xl btn-outline" @click="loadForm"> Go to Last Position </PrimaryButton>
        </div>
      </div>
    </section>
  </StoryLayout>
</template>
