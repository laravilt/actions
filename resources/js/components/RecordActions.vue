<template>
    <div v-if="actions && actions.length" :class="containerClass">
        <ActionButton
            v-for="action in processedActions"
            :key="action.name"
            v-bind="action"
            :data="actionData"
        />
    </div>
</template>

<script setup lang="ts">
import ActionButton from './ActionButton.vue';
import { computed } from 'vue';

interface RecordActionsProps {
    actions?: any[];
    record?: any;
    resourceName?: string;
    executionRoute?: string;
    variant?: 'inline' | 'stack' | 'grid';
    gap?: 'sm' | 'default' | 'lg';
    align?: 'start' | 'center' | 'end';
}

const props = withDefaults(defineProps<RecordActionsProps>(), {
    variant: 'inline',
    gap: 'sm',
    align: 'end',
});

// Process actions to include record data
const processedActions = computed(() => {
    if (!props.actions) return [];

    return props.actions.map(action => ({
        ...action,
        // Force icon variant for record actions unless explicitly set
        variant: action.variant || 'icon',
        size: action.size || 'default',
        // Add record context to action if needed
        recordId: props.record?.id,
        resourceName: props.resourceName,
        executionRoute: props.executionRoute,
    }));
});

// Data to pass to action execution
const actionData = computed(() => ({
    record: props.record,
    resourceName: props.resourceName,
}));

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
