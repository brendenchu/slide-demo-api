import { divide } from './math'

/**
 * Format a number as Canadian currency
 */
export function toMoney(value: number | null) {
  value = value || 0
  return value.toLocaleString('en-CA', {
    style: 'currency',
    currency: 'CAD',
  })
}

/**
 * Format a number as a percentage
 * @param value - The value to format
 * @param exact - If true, divides by 100 first
 */
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
