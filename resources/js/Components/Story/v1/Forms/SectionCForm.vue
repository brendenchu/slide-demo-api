<script lang="ts" setup>
  import { Slide } from '@/Components/Slide'
  import { InertiaForm, useForm } from '@inertiajs/vue3'
  import { Error, Fieldset, GroupWrapper, Label, Radio } from '@/Components/Form'
  import { onMounted, ref } from 'vue'
  import { Project, ProjectStep, SectionCFormFields } from '@/types'
  import { Action, Direction, SlideOptions } from '@/Components/Slide/types'
  import { delay, delta, nullifyFields, prevNextSteps, saveForm } from '@/Components/Story/v1/helpers'

  // The component's props
  const props = defineProps<{
    project: Project
    step: ProjectStep
    token: string
    page: number
    direction: Direction
    story: SectionCFormFields
  }>()

  // The form fields and their initial values
  const form: InertiaForm<SectionCFormFields> = useForm(props.story)

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
  const pages = ref<number>(9)

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
              form.post(
                route('story.publish', {
                  project: props.project,
                  token: props.token,
                })
              )
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
              🎉Wow! 🎉 You've made it to the last section. Capital C! Let's do capital cities! These questions will be
              multiple choice.
            </p>
            <div>
              <p><strong>What is the capital city of France?</strong></p>
            </div>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_1_a" v-model:checked="form.section_c_1" value="Barcelona" />
              <Label for="section_c_1_a">
                <span class="text-2xl">Barcelona</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_1_b" v-model:checked="form.section_c_1" value="Paris" />
              <Label for="section_c_1_b">
                <span class="text-2xl">Paris</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_1_c" v-model:checked="form.section_c_1" value="London" />
              <Label for="section_c_1_c">
                <span class="text-2xl">London</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_1_c" v-model:checked="form.section_c_1" value="Berlin" />
              <Label for="section_c_1_c">
                <span class="text-2xl">Berlin</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_1" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-2>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Japan?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_2_a" v-model:checked="form.section_c_2" value="Beijing" />
              <Label for="section_c_2_a">
                <span class="text-2xl">Beijing</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_2_b" v-model:checked="form.section_c_2" value="Bangkok" />
              <Label for="section_c_2_b">
                <span class="text-2xl">Bangkok</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_2_c" v-model:checked="form.section_c_2" value="Seoul" />
              <Label for="section_c_2_c">
                <span class="text-2xl">Seoul</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_2_d" v-model:checked="form.section_c_2" value="Tokyo" />
              <Label for="section_c_2_d">
                <span class="text-2xl">Tokyo</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_2" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-3>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Australia?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_3_a" v-model:checked="form.section_c_3" value="Canberra" />
              <Label for="section_c_3_a">
                <span class="text-2xl">Canberra</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_3_b" v-model:checked="form.section_c_3" value="Sydney" />
              <Label for="section_c_3_b">
                <span class="text-2xl">Sydney</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_3_c" v-model:checked="form.section_c_3" value="Melbourne" />
              <Label for="section_c_3_c">
                <span class="text-2xl">Melbourne</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_3_d" v-model:checked="form.section_c_3" value="Brisbane" />
              <Label for="section_c_3_d">
                <span class="text-2xl">Brisbane</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_3" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-4>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Canada?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_4_a" v-model:checked="form.section_c_4" value="Toronto" />
              <Label for="section_c_4_a">
                <span class="text-2xl">Toronto</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_4_b" v-model:checked="form.section_c_4" value="Vancouver" />
              <Label for="section_c_4_b">
                <span class="text-2xl">Vancouver</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_4_c" v-model:checked="form.section_c_4" value="Ottawa" />
              <Label for="section_c_4_c">
                <span class="text-2xl">Ottawa</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_4_d" v-model:checked="form.section_c_4" value="Montreal" />
              <Label for="section_c_4_d">
                <span class="text-2xl">Montreal</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_4" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-5>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of India?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_5_a" v-model:checked="form.section_c_5" value="Mumbai" />
              <Label for="section_c_5_a">
                <span class="text-2xl">Mumbai</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_5_b" v-model:checked="form.section_c_5" value="New Delhi" />
              <Label for="section_c_5_b">
                <span class="text-2xl">New Delhi</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_5_c" v-model:checked="form.section_c_5" value="Kolkata" />
              <Label for="section_c_5_c">
                <span class="text-2xl">Kolkata</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_5_d" v-model:checked="form.section_c_5" value="Chennai" />
              <Label for="section_c_5_d">
                <span class="text-2xl">Chennai</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_5" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-6>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Brazil?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_6_a" v-model:checked="form.section_c_6" value="Brasilia" />
              <Label for="section_c_6_a">
                <span class="text-2xl">Brasilia</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_6_b" v-model:checked="form.section_c_6" value="Rio de Janeiro" />
              <Label for="section_c_6_b">
                <span class="text-2xl">Rio de Janeiro</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_6_c" v-model:checked="form.section_c_6" value="São Paulo" />
              <Label for="section_c_6_c">
                <span class="text-2xl">São Paulo</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_6_d" v-model:checked="form.section_c_6" value="Salvador" />
              <Label for="section_c_6_d">
                <span class="text-2xl">Salvador</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_6" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-7>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Denmark?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_7_a" v-model:checked="form.section_c_7" value="Stockholm" />
              <Label for="section_c_7_a">
                <span class="text-2xl">Stockholm</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_7_b" v-model:checked="form.section_c_7" value="Oslo" />
              <Label for="section_c_7_b">
                <span class="text-2xl">Oslo</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_7_c" v-model:checked="form.section_c_7" value="Copenhagen" />
              <Label for="section_c_7_c">
                <span class="text-2xl">Copenhagen</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_7_d" v-model:checked="form.section_c_7" value="Helsinki" />
              <Label for="section_c_7_d">
                <span class="text-2xl">Helsinki</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_7" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-8>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>What is the capital city of Kenya?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_8_a" v-model:checked="form.section_c_8" value="Kinshasa" />
              <Label for="section_c_8_a">
                <span class="text-2xl">Kinshasa</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_8_b" v-model:checked="form.section_c_8" value="Mombasa" />
              <Label for="section_c_8_b">
                <span class="text-2xl">Mombasa</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_8_c" v-model:checked="form.section_c_8" value="Lagos" />
              <Label for="section_c_8_c">
                <span class="text-2xl">Lagos</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_8_d" v-model:checked="form.section_c_8" value="Nairobi" />
              <Label for="section_c_8_d">
                <span class="text-2xl">Nairobi</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_8" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
      <template #page-9>
        <Fieldset>
          <div class="prose prose-2xl pb-4">
            <p>
              <strong>Which is <u>NOT</u> one of the capital cities of South Africa?</strong>
            </p>
          </div>
          <GroupWrapper grid>
            <div class="flex gap-3">
              <Radio id="section_c_9_a" v-model:checked="form.section_c_9" value="Johannesburg" />
              <Label for="section_c_9_a">
                <span class="text-2xl">Johannesburg</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_9_b" v-model:checked="form.section_c_9" value="Cape Town" />
              <Label for="section_c_9_b">
                <span class="text-2xl">Cape Town</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_9_c" v-model:checked="form.section_c_9" value="Pretoria" />
              <Label for="section_c_9_c">
                <span class="text-2xl">Pretoria</span>
              </Label>
            </div>
            <div class="flex gap-3">
              <Radio id="section_c_9_d" v-model:checked="form.section_c_9" value="Bloemfontein" />
              <Label for="section_c_9_d">
                <span class="text-2xl">Bloemfontein</span>
              </Label>
            </div>
            <Error :message="form.errors.section_c_9" class="mt-1" />
          </GroupWrapper>
        </Fieldset>
      </template>
    </Slide>
  </form>
</template>

<style lang="postcss" scoped></style>
