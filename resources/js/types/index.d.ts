export interface User {
  id: string
  name: string
  email: string
  email_verified_at: string
  first_name: string
  last_name: string
  phone?: string
  address?: string
  city?: string
  state?: string
  zip?: string
  country?: string
  timezone?: string
  locale?: string
  currency?: string
  team: Team
  roles: string[]
  permissions: string[]
}

export interface Team {
  id: string
  slug: string
  name: string
  description?: string
  status?: string
  email?: string
  phone?: string
  website?: string
  current?: boolean
}

export interface Project {
  id: string
  slug: string
  name: string
  description?: string
  status?: string
  token?: string
}

export interface ProjectStep {
  id: string
  slug: string
  name: string
}

export interface Token {
  id: string
  expires_at: string
  revoked_at: string
  project: Project
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User
    team: Team
  }
  flash: {
    success?: string
    warning?: string
    error?: string
    info?: string
    message?: string
  }
  errors: {
    [key: string]: string[]
  }
}

export interface Tab {
  name: string
  href: string
  current: boolean
  data: Panel
}

export interface Panel {
  name: string
  description?: string
  lines: Line[]
}

export interface Line {
  name: string
  description?: string
  fields: Field[]
}

export interface PaginationLink {
  url?: string
  label: string
  active: boolean
}

/**
 * Form Types
 */

export type IntroFormFields = {
  intro_1: string | null
  intro_2: string | null
  intro_3: string | null
}

export type SectionAFormFields = {
  section_a_1: string | null
  section_a_2: string | null
  section_a_3: string | null
  section_a_4: string | null
  section_a_5: string | null
  section_a_6: string | null
}

export type SectionBFormFields = {
  section_b_1: string | null
  section_b_2: string | null
  section_b_3: string | null
  section_b_4: string | null
  section_b_5: string | null
  section_b_6: string | null
  section_b_7: string | null
  section_b_8: string | null
  section_b_9: string | null
}

export type SectionCFormFields = {
  section_c_1: string | null
  section_c_2: string | null
  section_c_3: string | null
  section_c_4: string | null
  section_c_5: string | null
  section_c_6: string | null
  section_c_7: string | null
  section_c_8: string | null
  section_c_9: string | null
}

export type AnyFormFields = IntroFormFields | SectionAFormFields | SectionBFormFields | SectionCFormFields

export type AllFormFields = IntroFormFields & SectionAFormFields & SectionBFormFields & SectionCFormFields

export type PaginatorLinks = Array<{ url: string | null; label: string; active: boolean }>

export type Paginator<T> = {
  current_page: number
  data: T[]
  first_page_url: string
  from: number
  last_page: number
  last_page_url: string
  links: PaginatorLinks
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number
  total: number
}
