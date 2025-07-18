/**
 * Defines an action that can be performed on a slide
 */
export type Action = {
  /* The label to display for the action */
  label?: string
  /* The condition that must be met for the action to take precedence */
  forced?: boolean
  /* The condition that must be met for the action to be prevented */
  blocked?: boolean
  /* The callback to execute when the action is performed */
  callback: () => void
}
/**
 * Defines the states for a slide
 */
export type State = 'idle' | 'transitioning' | 'animating' | 'disabled'

/**
 * Defines the direction of a slide
 */
export type Direction = 'next' | 'previous'

/**
 * Defines the options for a slide
 */
export type SlideOptions<T> = Record<string, T>
