<script lang="ts" setup>
  import { Slide } from '@/Components/Slide'
  import { InertiaForm, useForm } from '@inertiajs/vue3'
  import { Checkbox, Error, Field, Fieldset, GroupWrapper, Label } from '@/Components/Form'
  import { onMounted, ref } from 'vue'
  import { Project, ProjectStep, SectionAFormFields } from '@/types'
  import { Action, Direction, SlideOptions } from '@/Components/Slide/types'
  import { delay, delta, nullifyFields, prevNextSteps, saveForm } from '@/Components/Story/Form/helpers'

  // The component's props
  const props = defineProps<{
    project: Project
    step: ProjectStep
    token: string
    page: number
    direction: Direction
    story: SectionAFormFields
  }>()

  // The form fields and their initial values
  const form: InertiaForm<SectionAFormFields> = useForm(props.story)

  // The previous and next steps
  const steps = prevNextSteps(props.step)

  // The fields that are toggled by checkboxes, grouped by page
  const toggledFields: Record<number, Record<string, string>> = {
    1: {
      section_a_1: 'section_a_4',
      section_a_2: 'section_a_5',
      section_a_3: 'section_a_6',
    },
  }
  // The current page
  const current = ref<number>(0)

  // The previous page
  const previous = ref<number>(0)

  // The direction of the slide
  const formDirection = ref<Direction>('next')

  // The total number of pages
  const pages = ref<number>(2)

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
            <p>
              Now, let's try a little experiment. Select one or more checkboxes below. You may also select none, if you
              prefer.
            </p>
          </div>
          <GroupWrapper>
            <div class="flex gap-3">
              <Checkbox id="section_a_1" v-model:checked="form.section_a_1" :value="1" />
              <Label for="section_a_1">
                <strong class="text-2xl">Checkbox A1</strong>
              </Label>
            </div>
            <div class="flex gap-3">
              <Checkbox id="section_a_2" v-model:checked="form.section_a_2" :value="1" />
              <Label for="section_a_2">
                <strong class="text-2xl">Checkbox A2</strong>
              </Label>
            </div>
            <div class="flex gap-3">
              <Checkbox id="section_a_3" v-model:checked="form.section_a_3" :value="1" />
              <Label for="section_a_3">
                <strong class="text-2xl">Checkbox A3</strong>
              </Label>
            </div>
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-2>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              Depending on the checkboxes you selected, you will see one or more questions below. If you didn't select
              any checkboxes, you would have skipped to the next section.
            </p>
          </div>
          <GroupWrapper>
            <div v-if="form.section_a_1">
              <Label for="section_a_4">
                <strong>What is the current year?</strong>
                <small> You see this question because you ticked Checkbox A1. </small>
              </Label>
              <Field
                id="section_a_4"
                v-model="form.section_a_4"
                aria-placeholder="YYYY"
                class="form-field"
                placeholder="YYYY"
                type="number"
              />
              <Error :message="form.errors.section_a_4" class="mt-1" />
            </div>
            <div v-if="form.section_a_2">
              <Label for="section_a_5">
                <strong>What is the current month?</strong>
                <small> You see this question because you ticked Checkbox A2. </small>
              </Label>
              <Field
                id="section_a_5"
                v-model="form.section_a_5"
                aria-placeholder="MM"
                class="form-field"
                max="12"
                min="1"
                placeholder="MM"
                type="number"
              />
              <Error :message="form.errors.section_a_5" class="mt-1" />
            </div>
            <div v-if="form.section_a_3">
              <Label for="section_a_6">
                <strong>What is the current day?</strong>
                <small> You see this question because you ticked Checkbox A3. </small>
              </Label>
              <Field
                id="section_a_6"
                v-model="form.section_a_6"
                aria-placeholder="DD"
                class="form-field"
                max="31"
                min="1"
                placeholder="DD"
                type="number"
              />
              <Error :message="form.errors.section_a_6" class="mt-1" />
            </div>
          </GroupWrapper>
        </Fieldset>
      </template>
    </Slide>
  </form>
</template>

<style lang="postcss" scoped></style>
