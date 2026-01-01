import { Project, ProjectStep } from '@/types'
import axios from 'axios'
import { delay } from '@/utils/ui'

/**
 * Get the previous and next steps for navigation
 */
export function prevNextSteps(step: ProjectStep): { previous: string | null; next: string | null } {
  switch (String(step.id)) {
    case 'intro':
      return { previous: null, next: 'section-a' }
    case 'section-a':
      return { previous: 'intro', next: 'section-b' }
    case 'section-b':
      return { previous: 'section-a', next: 'section-c' }
    case 'section-c':
      return { previous: 'section-b', next: null }
    default:
      return { previous: null, next: null }
  }
}

/**
 * Complete the story and redirect to the complete page
 */
export function completeStory(project: Project, token: string) {
  // set story to complete
  axios
    .post(
      route('api.publish-story', {
        project,
        token,
      })
    )
    .then((response) => {
      // If the response is successful, redirect to the dashboard
      if (response.status === 200) {
        delay().then(() => (window.location.href = route('story.complete', { project, token })))
      }
    })
}
