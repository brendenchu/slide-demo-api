<script lang="ts" setup>
  import { ref } from 'vue'
  import { Head, useForm } from '@inertiajs/vue3'
  import StoryLayout from '@/Layouts/StoryLayout.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import SecondaryButton from '@/Components/SecondaryButton.vue'

  const props = defineProps<{
    terms: {
      id: number
      version: string
    }
  }>()

  const form = useForm({
    confirmation: false,
  })

  const accepted = ref(false)

  const submit = () => {
    if (!accepted.value) {
      return
    }

    form.post(
      route('terms.accept', {
        terms: props.terms.id,
      })
    )
  }

  const logoutUser = () => {
    form.post(route('logout'))
  }
</script>

<template>
  <Head title="Accept Terms of Service" />

  <StoryLayout>
    <section class="stretched">
      <div class="stretched contained centered">
        <div class="prose">
          <h1>Terms of Service</h1>
          <div class="border rounded-2xl p-8 shadow bg-base-100">
            <form class="flex gap-3 items-center justify-between" @submit.prevent="submit">
              <label>
                <input
                  v-model="accepted"
                  class="checkbox checkbox-lg checkbox-primary mr-2"
                  name="confirmation"
                  type="checkbox"
                />
                By checking this box, I agree to the latest terms of service.*
              </label>
              <div class="flex items-center gap-3">
                <PrimaryButton :disabled="!accepted">Continue</PrimaryButton>
              </div>
            </form>
            <div class="mt-4 text-right">
              <SecondaryButton class="btn btn-sm btn-outline" @click.prevent="logoutUser">
                <small>Actually, just log me out.</small>
              </SecondaryButton>
            </div>
          </div>
          <p>
            <small>
              * There are no terms of service at this time. This is a demonstration of using back-end middleware to
              require user acceptance of terms before proceeding.
            </small>
          </p>
        </div>
      </div>
    </section>
  </StoryLayout>
</template>

<style lang="postcss" scoped></style>
