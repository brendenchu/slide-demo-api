import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

/**
 * Merge Tailwind CSS classes with clsx
 */
export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

/**
 * Delay execution for a specified number of milliseconds
 */
export function delay(ms: number = 500) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}
