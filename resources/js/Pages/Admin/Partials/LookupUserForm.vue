<script setup lang="ts">
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import { useForm } from '@inertiajs/vue3'
  import InputLabel from '@/Components/Form/FormLabel.vue'
  import InputField from '@/Components/Form/FormField.vue'
  import InputError from '@/Components/Form/FormError.vue'

  const form = useForm({
    email: '',
  })

  const submit = () => {
    form.post(route('admin.users.search'), {
      preserveScroll: true,
      onFinish: () => {
        // form.reset('email')
      },
    })
  }
</script>

<template>
  <form class="flex flex-col gap-3" @submit.prevent="submit">
    <div class="flex flex-col gap-2 md:w-1/2">
      <InputLabel for="email" value="Email" class="sr-only" />

      <InputField
        id="email"
        v-model="form.email"
        type="text"
        required
        autocomplete="username"
        placeholder="Email"
        class="w-full"
      />

      <InputError class="mt-1" :message="form.errors.email" />
    </div>
    <div>
      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing"> Search</PrimaryButton>
    </div>
  </form>
</template>

<style scoped lang="postcss"></style>
