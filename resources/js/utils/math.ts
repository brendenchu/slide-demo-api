/**
 * Add multiple numbers together, treating null as 0
 */
export function add(...terms: (number | null)[]) {
  let result = 0
  terms.forEach((value) => {
    result += value || 0
  })
  return result
}

/**
 * Subtract multiple numbers from a base term, treating null as 0
 */
export function subtract(base_term: number | null, ...terms: (number | null)[]) {
  let result = base_term || 0
  terms.forEach((value) => {
    result -= value || 0
  })
  return result
}

/**
 * Multiply multiple numbers together, treating null or 0 as 0
 */
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

/**
 * Divide numerator by denominator, treating null or 0 as 0
 */
export function divide(numerator: number | null, denominator: number | null) {
  if (!numerator || !denominator) {
    return 0
  }
  return numerator / denominator
}
