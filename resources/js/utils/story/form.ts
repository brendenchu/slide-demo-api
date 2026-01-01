import { InertiaForm } from '@inertiajs/vue3'
import { AnyFormFields, Project, ProjectStep } from '@/types'

/**
 * Save form data to the server
 */
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

/**
 * Count the number of checked checkboxes in a group
 */
export function numChecked(group: string[]): number {
  let result = 0
  group.forEach((input) => {
    if ((document.getElementById(input) as HTMLInputElement)?.checked) {
      result++
    }
  })
  return result
}

/**
 * Calculate the page delta based on toggled fields
 */
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

/**
 * Nullify form fields based on checkbox state
 */
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
