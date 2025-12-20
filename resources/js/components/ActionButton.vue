<template>
    <TooltipProvider v-if="tooltip">
        <Tooltip>
            <TooltipTrigger as-child>
                <component
                    :is="componentType"
                    :type="type"
                    :variant="buttonVariant"
                    :size="variant === 'icon' ? 'icon' : size"
                    :disabled="disabled || isLoading"
                    :class="cn(buttonClass, props.class)"
                    @click="handleClick"
                    :href="url"
                    :target="openUrlInNewTab ? '_blank' : undefined"
                >
                    <Spinner v-if="isLoading" class="size-3" />
                    <component
                        v-else-if="icon"
                        :is="getIconComponent(icon)"
                        :class="iconClass"
                    />
                    <span v-if="label && variant !== 'icon'">{{ label }}</span>
                </component>
            </TooltipTrigger>
            <TooltipContent>
                {{ tooltip }}
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>

    <component
        v-else
        :is="componentType"
        :type="type"
        :variant="buttonVariant"
        :size="variant === 'icon' ? 'icon' : size"
        :disabled="disabled || isLoading"
        :class="cn(buttonClass, props.class)"
        @click="handleClick"
        :href="url"
        :target="openUrlInNewTab ? '_blank' : undefined"
    >
        <Spinner v-if="isLoading" class="size-3" />
        <component
            v-else-if="icon"
            :is="getIconComponent(icon)"
            :class="iconClass"
        />
        <span v-if="label && variant !== 'icon'">{{ label }}</span>
    </component>

    <!-- Action Confirmation Modal -->
    <Dialog v-if="!slideOver" v-model:open="showModal">
        <DialogContent :class="modalWidthClass">
            <DialogHeader :class="hasFormOrInfolistSchema ? 'text-start' : 'text-center sm:text-center'">
                <div v-if="modalIcon" class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full" :class="modalIconClass">
                    <component
                        :is="getIconComponent(modalIcon)"
                        class="size-6"
                    />
                </div>
                <DialogTitle v-if="modalHeading" :class="hasFormOrInfolistSchema ? 'text-start' : 'text-center'">{{ modalHeading }}</DialogTitle>
                <DialogDescription v-if="modalDescription" :class="hasFormOrInfolistSchema ? 'text-start' : 'text-center'">{{ modalDescription }}</DialogDescription>
            </DialogHeader>

            <!-- Modal Infolist Schema (for view only) -->
            <div v-if="modalInfolistSchema && modalInfolistSchema.length" class="-mx-6 px-6 py-4 max-h-[60vh] overflow-y-auto scroll-smooth overscroll-contain">
                <InfoList :schema="filledInfolistSchema" />
            </div>

            <!-- Modal Form Schema -->
            <div v-else-if="modalFormSchema && modalFormSchema.length" class="-mx-6 px-6 py-4 max-h-[60vh] overflow-y-auto scroll-smooth overscroll-contain">
                <ErrorProvider :errors="page.props.errors as Record<string, string | string[]>">
                    <Form ref="modalFormRef" :schema="modalFormSchema" v-model="formData" :disabled="isViewOnly" :form-controller="modalFormController" />
                </ErrorProvider>
            </div>

            <!-- Modal Content -->
            <div v-if="modalContent" class="py-4 text-center" v-html="modalContent"></div>

            <DialogFooter :class="hasFormOrInfolistSchema ? 'sm:justify-end rtl:justify-start' : 'sm:justify-center'" class="gap-2">
                <Button
                    variant="outline"
                    @click="showModal = false"
                >
                    {{ translatedCancelLabel }}
                </Button>
                <Button
                    v-if="!isViewOnly && translatedConfirmLabel"
                    @click="executeAction"
                    :disabled="isLoading"
                    :variant="modalButtonVariant"
                    :class="modalButtonClass"
                >
                    <Spinner v-if="isLoading" class="size-3 me-2" />
                    {{ translatedConfirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Action Confirmation Slideover -->
    <Sheet v-else v-model:open="showModal">
        <SheetContent>
            <SheetHeader :class="hasFormSchema ? 'text-start' : 'text-center'">
                <div v-if="modalIcon" class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full" :class="modalIconClass">
                    <component
                        :is="getIconComponent(modalIcon)"
                        class="size-6"
                    />
                </div>
                <SheetTitle v-if="modalHeading" :class="hasFormSchema ? 'text-start' : 'text-center'">{{ modalHeading }}</SheetTitle>
                <SheetDescription v-if="modalDescription" :class="hasFormSchema ? 'text-start' : 'text-center'">{{ modalDescription }}</SheetDescription>
            </SheetHeader>

            <!-- Form Schema -->
            <div v-if="modalFormSchema && modalFormSchema.length" class="px-4 py-4 max-h-[70vh] overflow-y-auto scroll-smooth overscroll-contain">
                <ErrorProvider :errors="page.props.errors as Record<string, string | string[]>">
                    <Form ref="slideOverFormRef" :schema="modalFormSchema" v-model="formData" :disabled="isViewOnly" :form-controller="modalFormController" />
                </ErrorProvider>
            </div>

            <!-- Content -->
            <div v-if="modalContent" class="px-4 py-4 text-center" v-html="modalContent"></div>

            <SheetFooter :class="hasFormSchema ? 'sm:justify-end rtl:justify-start' : 'justify-center'" class="gap-2">
                <Button
                    variant="outline"
                    @click="showModal = false"
                >
                    {{ translatedCancelLabel }}
                </Button>
                <Button
                    v-if="!isViewOnly && translatedConfirmLabel"
                    @click="executeAction"
                    :disabled="isLoading"
                    :variant="modalButtonVariant"
                    :class="modalButtonClass"
                >
                    <Spinner v-if="isLoading" class="size-3 me-2" />
                    {{ translatedConfirmLabel }}
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>

<script setup lang="ts">
import { computed, ref, h, inject } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

const emit = defineEmits<{
    (e: 'action-complete', data?: any): void;
}>();
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import Form from '@laravilt/forms/components/Form.vue';
import ErrorProvider from '@laravilt/forms/components/ErrorProvider.vue';
import InfoList from '@laravilt/infolists/components/InfoList.vue';
import * as LucideIcons from 'lucide-vue-next';
import { useNotification } from '@laravilt/notifications/composables/useNotification';
import { useLocalization } from '@laravilt/support/composables/useLocalization';

// Get Inertia page for accessing validation errors
const page = usePage();

// Inject validateForm from parent Form (if available)
const validateForm = inject<(() => boolean) | undefined>('validateForm', undefined);

// Initialize notification
const { notify } = useNotification();

// Initialize localization
const { trans } = useLocalization();

interface ActionProps {
    name?: string;
    label?: string;
    color?: string;
    icon?: string;
    iconPosition?: 'before' | 'after';
    url?: string;
    openUrlInNewTab?: boolean;
    requiresConfirmation?: boolean;
    modalHeading?: string;
    modalDescription?: string;
    modalSubmitActionLabel?: string;
    modalCancelActionLabel?: string;
    errorNotificationTitle?: string;
    modalIcon?: string;
    modalIconColor?: string;
    modalFormSchema?: any[];
    modalFormController?: string; // Controller class for reactive fields in modal
    modalInfolistSchema?: any[]; // Infolist schema for view-only display
    modalContent?: string;
    modalWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl' | '5xl'; // Modal width
    slideOver?: boolean;
    disabled?: boolean;
    variant?: 'button' | 'icon' | 'link';
    size?: 'sm' | 'default' | 'lg';
    isOutlined?: boolean;
    tooltip?: string;
    hasAction?: boolean;
    actionUrl?: string;
    actionToken?: string;
    data?: Record<string, any>;
    externalFormData?: Record<string, any>;
    getFormData?: () => Record<string, any>;
    class?: string;
    isBulkAction?: boolean;
    deselectRecordsAfterCompletion?: boolean;
    type?: 'button' | 'submit' | 'reset';
    isSubmit?: boolean; // Whether this action should validate the form before executing
    preserveState?: boolean;
    preserveScroll?: boolean;
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    useAjax?: boolean; // Use fetch API instead of Inertia router (avoids page reload)
    isViewOnly?: boolean; // View-only mode - no submit, just display
}

const props = withDefaults(defineProps<ActionProps>(), {
    variant: 'button',
    size: 'default',
    iconPosition: 'before',
    isOutlined: false,
    disabled: false,
    hasAction: false,
    type: 'button',
    preserveState: true,
    preserveScroll: true,
    method: 'POST',
    useAjax: false,
    isViewOnly: false,
});

const isLoading = ref(false);
const showModal = ref(false);
const formData = ref({});
const modalFormRef = ref<any>(null);
const slideOverFormRef = ref<any>(null);

// Computed translated labels with fallback to props
const translatedCancelLabel = computed(() => props.modalCancelActionLabel || trans('actions::actions.buttons.cancel'));
const translatedConfirmLabel = computed(() => props.modalSubmitActionLabel || trans('actions::actions.buttons.confirm'));
const translatedErrorTitle = computed(() => props.errorNotificationTitle || trans('notifications::notifications.error'));

// Check if action has a form schema
const hasFormSchema = computed(() => {
    return props.modalFormSchema && props.modalFormSchema.length > 0;
});

// Check if action has an infolist schema
const hasInfolistSchema = computed(() => {
    return props.modalInfolistSchema && props.modalInfolistSchema.length > 0;
});

// Check if action has either form or infolist schema
const hasFormOrInfolistSchema = computed(() => {
    return hasFormSchema.value || hasInfolistSchema.value;
});

// Modal width class based on modalWidth prop
const modalWidthClass = computed(() => {
    const widthMap: Record<string, string> = {
        'sm': 'sm:max-w-md',
        'md': 'sm:max-w-lg',
        'lg': 'sm:max-w-2xl',
        'xl': 'sm:max-w-3xl',
        '2xl': 'sm:max-w-4xl',
        '3xl': 'sm:max-w-5xl',
        '4xl': 'sm:max-w-6xl',
        '5xl': 'sm:max-w-7xl',
    };
    return widthMap[props.modalWidth || 'md'] || 'sm:max-w-lg';
});

// Fill infolist schema with record data
const filledInfolistSchema = computed(() => {
    if (!props.modalInfolistSchema || !props.externalFormData) {
        return props.modalInfolistSchema || [];
    }

    // Deep clone and fill values from externalFormData
    return fillInfolistValues(props.modalInfolistSchema, props.externalFormData);
});

// Helper function to recursively fill infolist values
const fillInfolistValues = (schema: any[], data: Record<string, any>): any[] => {
    return schema.map(item => {
        const filled = { ...item };

        // If item has a name, set its value/state from data
        if (item.name && data[item.name] !== undefined) {
            filled.value = data[item.name];
            filled.state = data[item.name];
        }

        // Handle nested schema (for sections, grids, etc.)
        if (item.schema && Array.isArray(item.schema)) {
            filled.schema = fillInfolistValues(item.schema, data);
        }

        return filled;
    });
};

// Determine component type (always Button for consistent styling)
const componentType = computed(() => {
    return Button;
});

// Map color to button variant
const buttonVariant = computed(() => {
    // Icon variant should always use ghost (no background)
    if (props.variant === 'icon') return 'ghost';
    if (props.variant === 'link') return 'link';
    if (props.isOutlined) return 'outline';

    switch (props.color) {
        case 'primary':
            return 'default';
        case 'secondary':
            return 'secondary';
        case 'danger':
        case 'destructive':
            return 'destructive';
        case 'gray':
        case 'ghost':
            return 'ghost';
        case 'purple':
        case 'indigo':
        case 'success':
        case 'warning':
            // Custom colors will use 'default' variant with custom classes
            return 'default';
        default:
            return 'default';
    }
});

// Button classes
const buttonClass = computed(() => {
    const classes = ['cursor-pointer'];

    // Icon variant - no background, color on icon only
    if (props.variant === 'icon') {
        return cn(...classes); // Return with cursor-pointer, color will be on icon
    }

    // Add custom color classes for non-standard colors (non-icon variants)
    if (props.color === 'purple') {
        classes.push('bg-purple-600 hover:bg-purple-700 text-white dark:bg-purple-600 dark:hover:bg-purple-700');
    } else if (props.color === 'indigo') {
        classes.push('bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-700');
    } else if (props.color === 'success') {
        classes.push('bg-green-600 hover:bg-green-700 text-white dark:bg-green-600 dark:hover:bg-green-700');
    } else if (props.color === 'warning') {
        classes.push('bg-yellow-600 hover:bg-yellow-700 text-white dark:bg-yellow-600 dark:hover:bg-yellow-700');
    }

    return cn(...classes);
});

// Icon color classes (for icon variant)
const iconClass = computed(() => {
    const classes = ['size-4'];

    // Add order-last if icon position is after
    if (props.iconPosition === 'after') {
        classes.push('order-last');
    }

    // For icon variant, apply color to the icon
    if (props.variant === 'icon') {
        switch (props.color) {
            case 'primary':
                classes.push('text-primary');
                break;
            case 'secondary':
                classes.push('text-muted-foreground');
                break;
            case 'danger':
            case 'destructive':
                classes.push('text-destructive');
                break;
            case 'success':
                classes.push('text-green-600 dark:text-green-500');
                break;
            case 'warning':
                classes.push('text-yellow-600 dark:text-yellow-500');
                break;
            case 'purple':
                classes.push('text-purple-600 dark:text-purple-500');
                break;
            case 'indigo':
                classes.push('text-indigo-600 dark:text-indigo-500');
                break;
            case 'gray':
            case 'ghost':
                classes.push('text-muted-foreground');
                break;
            default:
                classes.push('text-foreground');
        }
    }

    return cn(...classes);
});

// Modal button variant (for confirmation modals - should reflect actual color, not ghost)
const modalButtonVariant = computed(() => {
    if (props.isOutlined) return 'outline';

    switch (props.color) {
        case 'primary':
            return 'default';
        case 'secondary':
            return 'secondary';
        case 'danger':
        case 'destructive':
            return 'destructive';
        case 'gray':
        case 'ghost':
            return 'ghost';
        case 'purple':
        case 'indigo':
        case 'success':
        case 'warning':
            // Custom colors will use 'default' variant with custom classes
            return 'default';
        default:
            return 'default';
    }
});

// Modal button classes (for confirmation modals)
const modalButtonClass = computed(() => {
    const classes = [];

    // Add custom color classes for non-standard colors
    if (props.color === 'purple') {
        classes.push('bg-purple-600 hover:bg-purple-700 text-white dark:bg-purple-600 dark:hover:bg-purple-700');
    } else if (props.color === 'indigo') {
        classes.push('bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-700');
    } else if (props.color === 'success') {
        classes.push('bg-green-600 hover:bg-green-700 text-white dark:bg-green-600 dark:hover:bg-green-700');
    } else if (props.color === 'warning') {
        classes.push('bg-yellow-600 hover:bg-yellow-700 text-white dark:bg-yellow-600 dark:hover:bg-yellow-700');
    }

    return cn(...classes);
});

// Modal icon background class
const modalIconClass = computed(() => {
    const colorMap: Record<string, string> = {
        primary: 'bg-primary/10 text-primary',
        secondary: 'bg-secondary/10 text-secondary',
        danger: 'bg-destructive/10 text-destructive',
        destructive: 'bg-destructive/10 text-destructive',
        purple: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
        gray: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };

    return colorMap[props.modalIconColor || props.color || 'primary'] || colorMap.primary;
});

// Get icon component from Lucide
const getIconComponent = (iconName: string) => {
    if (!iconName) return null;

    // Remove prefixes if any (heroicon-o-, heroicon-s-, lucide-, etc.)
    const cleanName = iconName
        .replace(/^(heroicon-[os]-|lucide-)/i, '')
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');

    // Try to get the icon from Lucide
    const iconComponent = (LucideIcons as any)[cleanName];

    return iconComponent || null;
};

// Get success message based on HTTP method
const getSuccessMessage = (method: string): string => {
    const methodUpper = method.toUpperCase();
    if (methodUpper === 'POST') {
        return trans('actions::actions.messages.created');
    } else if (methodUpper === 'PUT' || methodUpper === 'PATCH') {
        return trans('actions::actions.messages.updated');
    } else if (methodUpper === 'DELETE') {
        return props.isBulkAction
            ? trans('actions::actions.messages.bulk_deleted')
            : trans('actions::actions.messages.deleted');
    }
    return trans('actions::actions.messages.action_completed');
};

// Handle click
const handleClick = async (e: Event) => {
    // Show modal if requires confirmation OR has a form/infolist schema
    // This takes priority over direct navigation
    const hasModalContent = props.requiresConfirmation ||
        (props.modalFormSchema && props.modalFormSchema.length > 0) ||
        (props.modalInfolistSchema && props.modalInfolistSchema.length > 0);

    if (hasModalContent) {
        e.preventDefault();
        e.stopPropagation();

        // Initialize form data with external data if provided (for edit/view actions)
        if (props.externalFormData) {
            formData.value = { ...props.externalFormData };
        } else {
            formData.value = {};
        }

        showModal.value = true;
        return;
    }

    // If it's a URL action without backend action AND not a special method, navigate
    // (DELETE, PUT, PATCH need to go through executeAction)
    if (props.url && !props.hasAction && (!props.method || props.method === 'GET')) {
        e.preventDefault();
        // Open in new tab if specified (e.g., for file downloads)
        if (props.openUrlInNewTab) {
            window.open(props.url, '_blank');
        } else {
            router.visit(props.url);
        }
        return;
    }

    // If it's a link variant with a URL, just navigate using Inertia
    if (props.variant === 'link' && props.url && !props.actionToken) {
        e.preventDefault();
        router.visit(props.url);
        return;
    }

    // Only prevent default for non-button elements or when we have an action
    if (props.hasAction || hasUrlBasedAction.value) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Execute action directly
    await executeAction();
};

// Check if this action needs URL-based execution (URL + non-GET method)
const hasUrlBasedAction = computed(() => {
    return props.url && props.method && props.method !== 'GET';
});

// Execute the action
const executeAction = async () => {
    // If no closure action AND no URL-based action, just close modal
    if (!props.hasAction && !hasUrlBasedAction.value) {
        showModal.value = false;
        return;
    }

    // Validate modal form if it exists
    const formRef = props.slideOver ? slideOverFormRef.value : modalFormRef.value;
    if (formRef && typeof formRef.validateForm === 'function') {
        if (!formRef.validateForm()) {
            return;
        }
    }

    // Validate parent form before executing action ONLY for submit-type actions
    // Cancel buttons (type='button', isSubmit=false) should NOT trigger form validation
    const shouldValidateForm = props.isSubmit === true || props.type === 'submit';
    if (shouldValidateForm && validateForm && !validateForm()) {
        return;
    }

    // Determine which type of action this is
    const isClosureAction = !!props.actionUrl;  // Has actionUrl = closure-based action
    const executionUrl = props.actionUrl || props.url;

    // If action has URL, execute via backend
    if (executionUrl) {
        isLoading.value = true;

        try {
            // Collect form data - merge props.data with modal form data
            // props.data contains action-specific data (like importer class)
            // formData.value contains user input from modal form (like uploaded file)
            let actionData = {
                ...(props.data || {}),
                ...formData.value,
            };

            // If we have getFormData callback, merge that too
            if (props.getFormData) {
                actionData = {
                    ...actionData,
                    ...props.getFormData(),
                };
            }

            // For record actions, ensure record context is always included
            // This is needed for edit/delete actions to know which record to operate on
            if (props.data && props.data.record) {
                actionData = {
                    ...actionData,
                    record: props.data.record,
                    resourceName: props.data.resourceName,
                    model: props.data.model,
                };
            }

            // If action has a token (closure action), send it with data wrapped
            // Otherwise (URL action), just send the form data directly
            const requestData = props.actionToken
                ? { token: props.actionToken, data: actionData }
                : actionData;

            // Use AJAX (fetch) if useAjax is true - this avoids page reload
            if (props.useAjax && !isClosureAction) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const method = (props.method || 'POST').toUpperCase();

                const response = await fetch(executionUrl, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: method !== 'GET' ? JSON.stringify(requestData) : undefined,
                });

                const result = await response.json();

                if (response.ok) {
                    showModal.value = false;
                    formData.value = {};

                    // Show success notification based on method/action type
                    const successMessage = result.message || getSuccessMessage(method);
                    if (successMessage) {
                        notify({
                            title: trans('actions::actions.states.success'),
                            body: successMessage,
                            type: 'success',
                        });
                    }

                    // If this is a bulk action and should deselect records after completion
                    if (props.isBulkAction && props.deselectRecordsAfterCompletion === true) {
                        window.dispatchEvent(new CustomEvent('bulk-action-completed'));
                    }

                    // Emit action-complete event for parent components
                    emit('action-complete', result);
                } else {
                    // Handle validation errors
                    if (result.errors && typeof result.errors === 'object') {
                        const errorMessages = Object.values(result.errors).flat();
                        if (errorMessages.length > 0) {
                            const firstError = Array.isArray(errorMessages[0])
                                ? errorMessages[0][0]
                                : errorMessages[0];

                            notify({
                                title: translatedErrorTitle.value,
                                body: String(firstError),
                                type: 'error',
                            });
                        }
                    } else if (result.message) {
                        notify({
                            title: translatedErrorTitle.value,
                            body: result.message,
                            type: 'error',
                        });
                    }
                }

                isLoading.value = false;
                return;
            }

            // Use Inertia router (default behavior)
            const requestOptions: any = {
                // Preserve state on errors (validation errors should keep form data)
                // But on success, use the prop value
                preserveState: (page) => {
                    // If there are errors, always preserve state to keep form data
                    if (Object.keys(page.props.errors || {}).length > 0) {
                        return true;
                    }
                    // Otherwise, use the configured value
                    return props.preserveState;
                },
                preserveScroll: (page) => {
                    // If there are errors, always preserve scroll
                    if (Object.keys(page.props.errors || {}).length > 0) {
                        return true;
                    }
                    // Otherwise, use the configured value
                    return props.preserveScroll;
                },
                onSuccess: (page) => {
                    showModal.value = false;
                    formData.value = {};

                    // Check if the action returned a redirect URL (for closure actions)
                    if (isClosureAction && page?.props?.actionUpdatedData?.redirect) {
                        const redirectUrl = page.props.actionUpdatedData.redirect as string;
                        window.location.href = redirectUrl;
                        return;
                    }

                    // Close modal on success
                    showModal.value = false;
                    formData.value = {};

                    // If this is a bulk action and should deselect records after completion
                    if (props.isBulkAction && props.deselectRecordsAfterCompletion === true) {
                        window.dispatchEvent(new CustomEvent('bulk-action-completed'));
                    }

                    // Emit action-complete event for parent components
                    emit('action-complete', page?.props?.actionUpdatedData);

                    // If the action updated form data (via Set), merge it back into parent form
                    // Only check for updated data if preserveState is true (otherwise page.props might not exist)
                    if (props.preserveState && page?.props) {
                        const updatedData = page.props.actionUpdatedData as Record<string, any> | null;
                        if (updatedData && Object.keys(updatedData).length > 0) {
                            // Emit event to update parent form data
                            // This will be handled by Form
                            window.dispatchEvent(new CustomEvent('action-updated-data', {
                                detail: updatedData
                            }));
                        }
                    }
                },
                onError: (errors) => {
                    console.error('Action execution failed:', errors);

                    // Show error notifications for each error
                    if (errors && typeof errors === 'object') {
                        const errorMessages = Object.values(errors).flat();
                        if (errorMessages.length > 0) {
                            // Show first error as notification (avoid spam if many errors)
                            const firstError = Array.isArray(errorMessages[0])
                                ? errorMessages[0][0]
                                : errorMessages[0];

                            notify({
                                title: translatedErrorTitle.value,
                                body: String(firstError),
                                type: 'error',
                            });
                        }
                    }
                },
                onFinish: () => {
                    isLoading.value = false;
                },
            };

            // Use the appropriate HTTP method
            const method = (props.method || 'POST').toLowerCase() as 'get' | 'post' | 'put' | 'patch' | 'delete';

            router[method](
                executionUrl,
                requestData,
                requestOptions,
            );
        } catch (error) {
            console.error('Action execution error:', error);
            isLoading.value = false;
        }
    } else {
        showModal.value = false;
        formData.value = {};
    }
};
</script>
