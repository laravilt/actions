import ActionButton from './ActionButton';

export interface RecordActionsProps {
    actions?: any[];
    record?: any;
    resourceName?: string;
    modelClass?: string;
    executionRoute?: string;
    variant?: 'inline' | 'stack' | 'grid';
    gap?: 'sm' | 'default' | 'lg';
    align?: 'start' | 'center' | 'end';
    onActionComplete?: (data?: any) => void;
}

export default function RecordActions({
    actions,
    record,
    resourceName,
    modelClass,
    executionRoute,
    variant = 'inline',
    gap = 'sm',
    align = 'end',
    onActionComplete,
}: RecordActionsProps) {
    if (!(actions && actions.length)) {
        return null;
    }

    const handleActionComplete = (data?: any) => {
        onActionComplete?.(data);
    };

    // Check if a record is soft deleted (trashed)
    // Check for deleted_at field (soft deletes)
    const isRecordTrashed = record ? !!record.deleted_at : false;

    // Process actions to include record data and filter out hidden ones
    const processedActions = actions
        // Filter out hidden actions
        .filter((action: any) => !action.isHidden)
        // Filter based on soft delete visibility flags
        .filter((action: any) => {
            // If action should only be visible when record is trashed
            if (action.visibleWhenTrashed && action.hiddenWhenNotTrashed) {
                return isRecordTrashed;
            }
            // If action should be hidden when record is trashed (like regular edit/delete)
            if (action.hiddenWhenTrashed) {
                return !isRecordTrashed;
            }
            return true;
        })
        .map((action: any) => ({
            ...action,
            // Force icon variant for record actions unless explicitly set
            variant: action.variant || 'icon',
            size: action.size || 'default',
            // Don't preserve state for record actions so the table refreshes after action
            preserveState: action.preserveState ?? false,
            // Add record context to action if needed
            recordId: record?.id,
            resourceName,
            executionRoute,
            // Pass record data for edit/view modals to pre-fill forms
            // First check action-specific modalFormData (from fillForm), then externalFormData, then full record
            externalFormData: action.modalFormData || action.externalFormData || record,
        }));

    // Data to pass to action execution
    const actionData = {
        record,
        resourceName,
        model: modelClass,
    };

    const classes = ['flex'];

    // Variant
    if (variant === 'stack') {
        classes.push('flex-col');
    } else if (variant === 'grid') {
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
    classes.push(gapMap[gap]);

    // Align
    if (variant !== 'grid') {
        const alignMap = {
            start: 'items-start',
            center: 'items-center',
            end: 'items-end',
        };
        classes.push(alignMap[align]);
    }

    const containerClass = classes.join(' ');

    return (
        <div className={containerClass}>
            {processedActions.map((action: any) => (
                <ActionButton key={action.name} {...action} data={actionData} onActionComplete={handleActionComplete} />
            ))}
        </div>
    );
}
