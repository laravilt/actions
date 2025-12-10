<template>
    <div v-if="actions && actions.length" :class="containerClass">
        <ActionButton
            v-for="action in processedActions"
            :key="action.name"
            v-bind="action"
            :data="actionData"
            @action-complete="handleActionComplete"
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
    modelClass?: string;
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

const emit = defineEmits<{
    'action-complete': [data?: any]
}>();

const handleActionComplete = (data?: any) => {
    emit('action-complete', data);
};

// Check if a record is soft deleted (trashed)
const isRecordTrashed = computed(() => {
    if (!props.record) return false;
    // Check for deleted_at field (soft deletes)
    return !!props.record.deleted_at;
});

// Process actions to include record data and filter out hidden ones
const processedActions = computed(() => {
    if (!props.actions) return [];

    return props.actions
        // Filter out hidden actions
        .filter(action => !action.isHidden)
        // Filter based on soft delete visibility flags
        .filter(action => {
            // If action should only be visible when record is trashed
            if (action.visibleWhenTrashed && action.hiddenWhenNotTrashed) {
                return isRecordTrashed.value;
            }
            // If action should be hidden when record is trashed (like regular edit/delete)
            if (action.hiddenWhenTrashed) {
                return !isRecordTrashed.value;
            }
            return true;
        })
        .map(action => ({
            ...action,
            // Force icon variant for record actions unless explicitly set
            variant: action.variant || 'icon',
            size: action.size || 'default',
            // Don't preserve state for record actions so the table refreshes after action
            preserveState: action.preserveState ?? false,
            // Add record context to action if needed
            recordId: props.record?.id,
            resourceName: props.resourceName,
            executionRoute: props.executionRoute,
            // Pass record data for edit/view modals to pre-fill forms
            externalFormData: action.externalFormData || props.record,
        }));
});

// Data to pass to action execution
const actionData = computed(() => ({
    record: props.record,
    resourceName: props.resourceName,
    model: props.modelClass,
}));

const containerClass = computed(() => {
    const classes = ['flex'];

    // Variant
    if (props.variant === 'stack') {
        classes.push('flex-col');
    } else if (props.variant === 'grid') {
        classes.push('grid', 'grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4');
    } else {
        classes.push('flex-row', 'flex-nowrap');
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
