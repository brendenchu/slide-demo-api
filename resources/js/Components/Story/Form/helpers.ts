import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'
import { InertiaForm } from '@inertiajs/vue3'
import { AnyFormFields, Project, ProjectStep } from '@/types'
import axios from 'axios'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function delay(ms: number = 500) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

export function saveForm(
  form: InertiaForm<AnyFormFields>,
  options: {
    project: Project
    step: ProjectStep
    page: number
    token: string
  },
  onSuccess: () => void,
  onError?: () => void
) {
  form.post(route('story.save-responses', options), {
    preserveScroll: true,
    onSuccess: onSuccess,
    onError: onError,
  })
}

export function numChecked(group: string[]): number {
  let result = 0
  group.forEach((input) => {
    if ((document.getElementById(input) as HTMLInputElement)?.checked) {
      result++
    }
  })
  return result
}

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

export function delta(page: number, toggledFields?: Record<number, Record<string, string>>) {
  // set delta to 1
  let delta = 1

  // if current page is a key in the toggledFields object
  if (toggledFields && Object.keys(toggledFields).includes(page.toString())) {
    // If no checkboxes are checked, set delta to 2
    if (numChecked(Object.keys(toggledFields[page])) === 0) {
      delta++
    }
  }
  return delta
}

export function nullifyFields(
  form: InertiaForm<AnyFormFields>,
  toggledFields: Record<number, Record<string, string>>,
  page: number
) {
  // If any checkboxes are checked, set the fields that are toggled to null
  if (toggledFields[page]) {
    for (const [check, field] of Object.entries(toggledFields[page])) {
      if (!form[check as keyof AnyFormFields]) {
        form[field as keyof AnyFormFields] = null as unknown as AnyFormFields[keyof AnyFormFields]
      }
    }
  }
}

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

export function toMoney(value: number | null) {
  value = value || 0
  return value.toLocaleString('en-CA', {
    style: 'currency',
    currency: 'CAD',
  })
}

export function toPercent(value: number | null, exact: boolean = false) {
  value = value || 0

  if (exact) {
    value = divide(value, 100)
  }

  return value.toLocaleString('en-CA', {
    style: 'percent',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

export function add(...terms: (number | null)[]) {
  let result = 0
  terms.forEach((value) => {
    result += value || 0
  })
  return result
}

export function subtract(base_term: number | null, ...terms: (number | null)[]) {
  let result = base_term || 0
  terms.forEach((value) => {
    result -= value || 0
  })
  return result
}

export function multiply(...factors: (number | null)[]) {
  if (factors.length === 0 || factors.includes(null) || factors.includes(0)) {
    return 0
  }

  let result = 1
  factors.forEach((value) => {
    result *= value || 1
  })
  return result
}

export function divide(numerator: number | null, denominator: number | null) {
  if (!numerator || !denominator) {
    return 0
  }
  return numerator / denominator
}

export function back() {
  window.history.back()
}
