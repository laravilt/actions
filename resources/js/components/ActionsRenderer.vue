<template>
    <div v-if="actions && actions.length" :class="containerClass">
        <ActionButton
            v-for="action in actions"
            :key="action.name"
            v-bind="action"
        />
    </div>
</template>

<script setup lang="ts">
import ActionButton from './ActionButton.vue';
import { computed } from 'vue';

interface ActionsRendererProps {
    actions?: any[];
    variant?: 'inline' | 'stack' | 'grid';
    gap?: 'sm' | 'default' | 'lg';
    align?: 'start' | 'center' | 'end';
}

const props = withDefaults(defineProps<ActionsRendererProps>(), {
    variant: 'inline',
    gap: 'default',
    align: 'start',
});

const containerClass = computed(() => {
    const classes = ['flex'];

    // Variant
    if (props.variant === 'stack') {
        classes.push('flex-col');
    } else if (props.variant === 'grid') {
        classes.push('grid', 'grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4');
    } else {
        classes.push('flex-row', 'flex-wrap');
    }

    // Gap
    const gapMap = {
        sm: 'gap-1',
        default: 'gap-2',
        lg: 'gap-4',
    };
    classes.push(gapMap[props.gap]);

    // Align
    if (props.variant !== 'grid') {
        const alignMap = {
            start: 'items-start',
            center: 'items-center',
            end: 'items-end',
        };
        classes.push(alignMap[props.align]);
    }

    return classes.join(' ');
});
</script>
