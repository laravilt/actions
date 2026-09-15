// Laravilt Actions Package Entry Point
export { default as ActionButton } from './components/ActionButton';
export type { ActionButtonProps, ActionProps } from './components/ActionButton';
export { default as ActionsRenderer } from './components/ActionsRenderer';

export default {
    /**
     * The Vue plugin registers no global component names, so there is nothing to register here.
     */
    register(): void {
        // Intentionally empty
    },
};
