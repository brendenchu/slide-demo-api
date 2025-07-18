<script lang="ts" setup>
  import { Slide } from '@/Components/Slide'
  import { InertiaForm, useForm } from '@inertiajs/vue3'
  import { Error, Field, Fieldset, Label } from '@/Components/Form'
  import { onMounted, ref } from 'vue'
  import { IntroFormFields, Project, ProjectStep } from '@/types'
  import { Action, Direction, SlideOptions } from '@/Components/Slide/types'
  import { delay, delta, nullifyFields, prevNextSteps, saveForm } from '@/Components/Story/v1/helpers'

  // The component's props
  const props = defineProps<{
    project: Project
    step: ProjectStep
    token: string
    page: number
    direction: Direction
    story: IntroFormFields
  }>()

  // The form fields and their initial values
  const form: InertiaForm<IntroFormFields> = useForm(props.story)

  // The previous and next steps
  const steps = prevNextSteps(props.step)

  // The fields that are toggled by checkboxes, grouped by page
  const toggledFields: Record<number, Record<string, string>> = {}

  // The current page
  const current = ref<number>(0)

  // The previous page
  const previous = ref<number>(0)

  // The direction of the slide
  const formDirection = ref<Direction>('next')

  // The total number of pages
  const pages = ref<number>(1)

  // The slide actions
  const actions = ref<SlideOptions<Action>>({
    // The next action
    next: {
      label: 'Save & Continue »',
      callback: () => {
        // If any checkboxes are checked, set the fields that are toggled to null
        nullifyFields(form, toggledFields, current.value)
        // Save the form, then shift current page by the delta amount
        saveForm(
          form,
          {
            project: props.project,
            step: props.step,
            page: current.value,
            token: props.token,
          },
          async () => {
            current.value += delta(current.value, toggledFields)
            previous.value = current.value - 1
            formDirection.value = 'next'
            // If the current page is greater than the total number of pages, redirect to the next step
            if (current.value > pages.value) {
              await delay()
              window.location.href = route('story.form', {
                project: props.project,
                step: steps.next,
                token: props.token,
              })
            }
          }
        )
      },
    },
    // The previous action
    previous: {
      label: '« Go Back',
      callback: () => {
        current.value -= delta(current.value, toggledFields)
        previous.value = current.value - 1
        formDirection.value = 'previous'
      },
    },
  })

  // When the component is mounted, set the current, previous, and direction values
  onMounted(() => {
    current.value = props.page
    previous.value = current.value - 1
    formDirection.value = props.direction
  })
</script>

<template>
  <form class="stretched prose">
    <Slide :actions="actions" :current="current" :direction="formDirection" :pages="pages">
      <template #page-1>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>To start, let's get to know you a little better.</p>
          </div>
          <div>
            <Label for="intro_1">
              <strong>Your First Name</strong>
            </Label>
            <Field id="intro_1" v-model="form.intro_1" class="form-field" />
            <Error :message="form.errors.intro_1" class="mt-1" />
          </div>
          <div>
            <Label for="intro_2">
              <strong>Your Last Name</strong>
            </Label>
            <Field id="intro_2" v-model="form.intro_2" class="form-field" />
            <Error :message="form.errors.intro_2" class="mt-1" />
          </div>
          <div>
            <Label for="intro_1">
              <strong>Your Location</strong>
            </Label>
            <Field id="intro_3" v-model="form.intro_3" class="form-field" />
            <Error :message="form.errors.intro_3" class="mt-1" />
          </div>
        </Fieldset>
      </template>
    </Slide>
  </form>
</template>

<style lang="postcss" scoped></style>
