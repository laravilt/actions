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
        <DialogContent class="sm:max-w-md">
            <DialogHeader :class="hasFormSchema ? '' : 'text-center sm:text-center'">
                <div v-if="modalIcon" class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full" :class="modalIconClass">
                    <component
                        :is="getIconComponent(modalIcon)"
                        class="size-6"
                    />
                </div>
                <DialogTitle v-if="modalHeading" :class="hasFormSchema ? '' : 'text-center'">{{ modalHeading }}</DialogTitle>
                <DialogDescription v-if="modalDescription" :class="hasFormSchema ? '' : 'text-center'">{{ modalDescription }}</DialogDescription>
            </DialogHeader>

            <!-- Modal Form Schema -->
            <div v-if="modalFormSchema && modalFormSchema.length" class="py-4">
                <ErrorProvider :errors="page.props.errors as Record<string, string | string[]>">
                    <FormRenderer ref="modalFormRef" :schema="modalFormSchema" v-model="formData" />
                </ErrorProvider>
            </div>

            <!-- Modal Content -->
            <div v-if="modalContent" class="py-4 text-center" v-html="modalContent"></div>

            <DialogFooter :class="hasFormSchema ? 'sm:justify-end' : 'sm:justify-center'" class="gap-2">
                <Button
                    variant="outline"
                    @click="showModal = false"
                >
                    {{ modalCancelActionLabel || 'Cancel' }}
                </Button>
                <Button
                    @click="executeAction"
                    :disabled="isLoading"
                    :variant="modalButtonVariant"
                    :class="modalButtonClass"
                >
                    <Spinner v-if="isLoading" class="size-3 mr-2" />
                    {{ modalSubmitActionLabel || 'Confirm' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Action Confirmation Slideover -->
    <Sheet v-else v-model:open="showModal">
        <SheetContent>
            <SheetHeader :class="hasFormSchema ? '' : 'text-center'">
                <div v-if="modalIcon" class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full" :class="modalIconClass">
                    <component
                        :is="getIconComponent(modalIcon)"
                        class="size-6"
                    />
                </div>
                <SheetTitle v-if="modalHeading" :class="hasFormSchema ? '' : 'text-center'">{{ modalHeading }}</SheetTitle>
                <SheetDescription v-if="modalDescription" :class="hasFormSchema ? '' : 'text-center'">{{ modalDescription }}</SheetDescription>
            </SheetHeader>

            <!-- Form Schema -->
            <div v-if="modalFormSchema && modalFormSchema.length" class="px-4 py-4">
                <ErrorProvider :errors="page.props.errors as Record<string, string | string[]>">
                    <FormRenderer ref="slideOverFormRef" :schema="modalFormSchema" v-model="formData" />
                </ErrorProvider>
            </div>

            <!-- Content -->
            <div v-if="modalContent" class="px-4 py-4 text-center" v-html="modalContent"></div>

            <SheetFooter :class="hasFormSchema ? 'sm:justify-end' : 'justify-center'" class="gap-2">
                <Button
                    variant="outline"
                    @click="showModal = false"
                >
                    {{ modalCancelActionLabel || 'Cancel' }}
                </Button>
                <Button
                    @click="executeAction"
                    :disabled="isLoading"
                    :variant="modalButtonVariant"
                    :class="modalButtonClass"
                >
                    <Spinner v-if="isLoading" class="size-3 mr-2" />
                    {{ modalSubmitActionLabel || 'Confirm' }}
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>

<script setup lang="ts">
import { computed, ref, h, inject } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Spinner from '@/components/ui/spinner/Spinner.vue';
import FormRenderer from '@laravilt/forms/components/FormRenderer.vue';
import ErrorProvider from '@laravilt/forms/components/ErrorProvider.vue';
import * as LucideIcons from 'lucide-vue-next';
import { useNotification } from '@laravilt/notifications/composables/useNotification';

// Get Inertia page for accessing validation errors
const page = usePage();

// Inject validateForm from parent FormRenderer (if available)
const validateForm = inject<(() => boolean) | undefined>('validateForm', undefined);

// Initialize notification
const { notify } = useNotification();

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
    modalIcon?: string;
    modalIconColor?: string;
    modalFormSchema?: any[];
    modalContent?: string;
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
    preserveState?: boolean;
    preserveScroll?: boolean;
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
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
});

const isLoading = ref(false);
const showModal = ref(false);
const formData = ref({});
const modalFormRef = ref<any>(null);
const slideOverFormRef = ref<any>(null);

// Check if action has a form schema
const hasFormSchema = computed(() => {
    return props.modalFormSchema && props.modalFormSchema.length > 0;
});

// Determine component type (Button or Link)
const componentType = computed(() => {
    if (props.url && !props.hasAction) {
        return props.openUrlInNewTab ? 'a' : Button;
    }
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
    const classes = [];

    // Icon variant - no background, color on icon only
    if (props.variant === 'icon') {
        return cn(...classes); // Return empty for now, color will be on icon
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

// Handle click
const handleClick = async (e: Event) => {
    // If it's a URL action without backend action, navigate using Inertia
    if (props.url && !props.hasAction) {
        e.preventDefault();
        router.visit(props.url);
        return;
    }

    // If it's a link variant with a URL, just navigate using Inertia
    if (props.variant === 'link' && props.url && !props.actionToken) {
        e.preventDefault();
        router.visit(props.url);
        return;
    }

    // Only prevent default for non-button elements or when we have an action
    if (props.hasAction) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Show modal if requires confirmation OR has a form schema
    if (props.requiresConfirmation || (props.modalFormSchema && props.modalFormSchema.length > 0)) {
        showModal.value = true;
        return;
    }

    // Execute action directly
    await executeAction();
};

// Execute the action
const executeAction = async () => {
    // If no action to execute, just close modal
    if (!props.hasAction) {
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

    // Validate parent form before executing action (if validateForm is available from parent)
    if (validateForm && !validateForm()) {
        return;
    }

    // Determine which type of action this is
    const isClosureAction = !!props.actionUrl;  // Has actionUrl = closure-based action
    const executionUrl = props.actionUrl || props.url;

    // If action has URL, execute via backend
    if (executionUrl) {
        isLoading.value = true;

        try {
            // Collect form data from parent form if getFormData is available
            let actionData = formData.value;

            if (props.getFormData) {
                actionData = props.getFormData();
            } else if (props.externalFormData) {
                actionData = props.externalFormData;
            } else if (props.data) {
                actionData = props.data;
            }

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

                    // If the action updated form data (via Set), merge it back into parent form
                    // Only check for updated data if preserveState is true (otherwise page.props might not exist)
                    if (props.preserveState && page?.props) {
                        const updatedData = page.props.actionUpdatedData as Record<string, any> | null;
                        if (updatedData && Object.keys(updatedData).length > 0) {
                            // Emit event to update parent form data
                            // This will be handled by FormRenderer
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
                                title: 'Error',
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

            // Only use 'only' parameter for closure-based actions when preserveState is true
            // If preserveState is false, we want a full page reload to get session flashes and updated props
            if (isClosureAction && props.preserveState !== false) {
                requestOptions.only = ['actionUpdatedData', 'notifications', 'errors'];
            }

            // If action has a token (closure action), send it with data wrapped
            // Otherwise (URL action), just send the form data directly
            const requestData = props.actionToken
                ? { token: props.actionToken, data: actionData }
                : actionData;

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
