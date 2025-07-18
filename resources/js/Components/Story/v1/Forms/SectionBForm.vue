<script lang="ts" setup>
  import { Slide } from '@/Components/Slide'
  import { InertiaForm, useForm } from '@inertiajs/vue3'
  import { Error, Field, Fieldset, Label } from '@/Components/Form'
  import { onMounted, ref } from 'vue'
  import { Project, ProjectStep, SectionBFormFields } from '@/types'
  import { Action, Direction, SlideOptions } from '@/Components/Slide/types'
  import { delay, delta, nullifyFields, prevNextSteps, saveForm } from '@/Components/Story/v1/helpers'

  // The component's props
  const props = defineProps<{
    project: Project
    step: ProjectStep
    token: string
    page: number
    direction: Direction
    story: SectionBFormFields
  }>()

  // The form fields and their initial values
  const form: InertiaForm<SectionBFormFields> = useForm(props.story)

  // The fields that are toggled by checkboxes, grouped by page
  const toggledFields: Record<number, Record<string, string>> = {}

  // The previous and next steps
  const steps = prevNextSteps(props.step)

  // The current page
  const current = ref<number>(0)

  // The previous page
  const previous = ref<number>(0)

  // The direction of the slide
  const formDirection = ref<Direction>('next')

  // The total number of pages
  const pages = ref<number>(3)

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
      forced: true, // Always show the previous button
      callback: async () => {
        current.value -= delta(current.value - 2, toggledFields)
        previous.value = current.value - 1
        formDirection.value = 'previous'
        // If the current page is 0, redirect to the previous step
        if (current.value < 1) {
          await delay()
          window.location.href = route('story.form', {
            project: props.project,
            step: steps.previous,
            token: props.token,
            page: 1,
            direction: formDirection.value,
          })
        }
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
            <p>Okay, let's do some quick maths.</p>
          </div>
          <div>
            <Label for="section_b_1">
              <strong>1 + 1?</strong>
            </Label>
            <Field id="section_b_1" v-model="form.section_b_1" class="form-field" type="number" />
            <Error :message="form.errors.section_b_1" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_2">
              <strong>2 - 6?</strong>
            </Label>
            <Field id="section_b_2" v-model="form.section_b_2" class="form-field" type="number" />
            <Error :message="form.errors.section_b_2" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_3">
              <strong>3 &times; 3?</strong>
            </Label>
            <Field id="section_b_3" v-model="form.section_b_3" class="form-field" type="number" />
            <Error :message="form.errors.section_b_3" class="mt-1" />
          </div>
        </Fieldset>
      </template>
      <template #page-2>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>How about some <em>slightly</em> more challenging math problems.</p>
            <p>You are permitted to use a calculator.</p>
          </div>
          <div>
            <Label for="section_b_4">
              <strong>12 &divide; 4?</strong>
            </Label>
            <Field id="section_b_4" v-model="form.section_b_4" class="form-field" />
            <Error :message="form.errors.section_b_4" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_5">
              <strong>3<sup>3</sup>, or 3 cubed?</strong>
            </Label>
            <Field id="section_b_5" v-model="form.section_b_5" class="form-field" />
            <Error :message="form.errors.section_b_5" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_6">
              <strong>5!, or 5 factorial?</strong>
            </Label>
            <Field id="section_b_6" v-model="form.section_b_6" class="form-field" />
            <Error :message="form.errors.section_b_6" class="mt-1" />
          </div>
        </Fieldset>
      </template>
      <template #page-3>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>How many&hellip;</p>
          </div>
          <div>
            <Label for="section_b_7">
              <strong>&hellip;sides are on a heptagon?</strong>
            </Label>
            <Field id="section_b_7" v-model="form.section_b_7" class="form-field" />
            <Error :message="form.errors.section_b_7" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_8">
              <strong>&hellip;degrees are in a right angle?</strong>
            </Label>
            <Field id="section_b_8" v-model="form.section_b_8" class="form-field" />
            <Error :message="form.errors.section_b_8" class="mt-1" />
          </div>
          <div>
            <Label for="section_b_9">
              <strong>&hellip;days are in a leap year?</strong>
            </Label>
            <Field id="section_b_9" v-model="form.section_b_9" class="form-field" />
            <Error :message="form.errors.section_b_9" class="mt-1" />
          </div>
        </Fieldset>
      </template>
    </Slide>
  </form>
</template>

<style lang="postcss" scoped></style>
