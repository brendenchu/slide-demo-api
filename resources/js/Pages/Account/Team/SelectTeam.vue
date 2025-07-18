<script setup lang="ts">
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Head } from '@inertiajs/vue3'
  import { Team } from '@/types'
  import axios from 'axios'
  import { computed, ref } from 'vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'

  /**
   * Define the props for the component.
   */
  const props = defineProps<{
    teams: Team[]
  }>()

  /**
   * Define the data for the component.
   */
  const currentTeam = ref<Team | undefined>(props.teams.find((team) => team.current))

  /**
   * Fetch the current team from the session.
   */
  const currentTeamName = computed(() => {
    return currentTeam.value?.name ?? 'None'
  })

  /**
   * Fetch the current team from the session.
   * @returns
   */
  function teamFromSession() {
    axios.get(route('api.get-current-team')).then((response) => {
      if (response.status === 200) {
        currentTeam.value = response.data.current_team
      }
    })
  }

  /**
   * Select a team.
   * @param team
   * @returns
   */
  function selectTeam({ slug }: Team) {
    axios.post(route('api.set-current-team', { team: slug })).then((response) => {
      if (response.status === 200) {
        teamFromSession()
      }
    })
  }
</script>

<template>
  <Head title="Select Team" />
  <AuthenticatedLayout>
    <div class="p-12 lg:px-0">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col lg:grid lg:grid-cols-3 gap-4">
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg lg:col-span-3">
          <strong>Current Team:</strong> {{ currentTeamName }}
        </div>
        <div v-for="team in props.teams" :key="team.id" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
          <section class="relative min-h-[160px]">
            <header>
              <h2 class="text-lg font-medium text-gray-900">{{ team.name }}</h2>
              <p class="mt-1 text-sm text-gray-500">{{ team.description }}</p>
            </header>
            <div class="flex space-x-2 absolute bottom-0">
              <PrimaryButton
                :disabled="team.id === currentTeam?.id"
                :class="{
                  'bg-slate-500 hover:bg-slate-500 focus:bg-slate-500 active:bg-slate-500': team.id === currentTeam?.id,
                }"
                @click="selectTeam(team)"
                >{{ team.id === currentTeam?.id ? 'Selected' : 'Select' }}
              </PrimaryButton>
            </div>
          </section>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
