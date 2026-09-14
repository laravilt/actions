import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Spinner } from '@/components/ui/spinner';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { router, usePage } from '@inertiajs/react';
import ErrorProvider from '@laravilt/forms/components/ErrorProvider';
import Form from '@laravilt/forms/components/Form';
import InfoList from '@laravilt/infolists/components/InfoList';
import { useNotification } from '@laravilt/notifications/composables/useNotification';
import { useSchemaContext } from '@laravilt/support/composables/contexts';
import { useLatest } from '@laravilt/support/composables/hooks';
import { useLocalization } from '@laravilt/support/composables/useLocalization';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { useCallback, useRef, useState, type MouseEvent as ReactMouseEvent } from 'react';

export interface ActionProps {
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

export interface ActionButtonProps extends ActionProps {
    /** Vue `emit('action-complete', data)`. */
    onActionComplete?: (data?: any) => void;
    /** React spelling of the Vue `class` prop; merged with `class`. */
    className?: string;
    /** Extra server payload keys (recordId, executionRoute, …) are accepted and ignored, like Vue fall-through attrs. */
    [key: string]: any;
}

type ButtonVariant = 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';

// Map color to button variant
const colorToVariant = (color?: string): ButtonVariant => {
    switch (color) {
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
};

// Custom color classes for non-standard colors
const customColorClass = (color?: string): string | null => {
    if (color === 'purple') {
        return 'bg-purple-600 hover:bg-purple-700 text-white dark:bg-purple-600 dark:hover:bg-purple-700';
    } else if (color === 'indigo') {
        return 'bg-indigo-600 hover:bg-indigo-700 text-white dark:bg-indigo-600 dark:hover:bg-indigo-700';
    } else if (color === 'success') {
        return 'bg-green-600 hover:bg-green-700 text-white dark:bg-green-600 dark:hover:bg-green-700';
    } else if (color === 'warning') {
        return 'bg-yellow-600 hover:bg-yellow-700 text-white dark:bg-yellow-600 dark:hover:bg-yellow-700';
    }
    return null;
};

// Helper function to recursively fill infolist values
const fillInfolistValues = (schema: any[], data: Record<string, any>): any[] => {
    return schema.map((item) => {
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

// First error message out of an errors bag
const firstErrorMessage = (errors: Record<string, any>): any => {
    const errorMessages = Object.values(errors).flat();
    if (errorMessages.length === 0) {
        return undefined;
    }
    return Array.isArray(errorMessages[0]) ? errorMessages[0][0] : errorMessages[0];
};

export default function ActionButton(props: ActionButtonProps) {
    const {
        label,
        color,
        icon,
        url,
        openUrlInNewTab,
        modalHeading,
        modalDescription,
        modalSubmitActionLabel,
        modalCancelActionLabel,
        modalIcon,
        modalIconColor,
        modalFormSchema,
        modalFormController,
        modalInfolistSchema,
        modalContent,
        modalWidth,
        slideOver,
        tooltip,
        externalFormData,
        variant = 'button',
        size = 'default',
        iconPosition = 'before',
        isOutlined = false,
        disabled = false,
        hasAction = false,
        type = 'button',
        preserveState = true,
        preserveScroll = true,
        method = 'POST',
        useAjax = false,
        isViewOnly = false,
    } = props;

    // Props with defaults applied; read through `latest` inside async callbacks (Vue reads `props.x` at call time)
    const latest = useLatest({
        ...props,
        variant,
        size,
        iconPosition,
        isOutlined,
        disabled,
        hasAction,
        type,
        preserveState,
        preserveScroll,
        method,
        useAjax,
        isViewOnly,
    });

    // Get Inertia page for accessing validation errors
    const page = usePage();

    // Inject validateForm from parent Form (if available)
    const { validateForm } = useSchemaContext();

    // Initialize notification
    const { notify } = useNotification();

    // Initialize localization
    const { trans } = useLocalization();

    const [isLoading, setIsLoading] = useState(false);
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState<Record<string, any>>({});
    const formDataRef = useRef<Record<string, any>>({});
    const modalFormRef = useRef<any>(null);
    const slideOverFormRef = useRef<any>(null);

    const updateFormData = useCallback((value: Record<string, any>) => {
        formDataRef.current = value;
        setFormData(value);
    }, []);

    const notifyMessage = (payload: { title: string; body: string; type: 'success' | 'error' }) => {
        notify(payload as any);
    };

    // Translated labels with fallback to props
    const translatedCancelLabel = modalCancelActionLabel || trans('actions::actions.buttons.cancel');
    const translatedConfirmLabel = modalSubmitActionLabel || trans('actions::actions.buttons.confirm');
    const getTranslatedErrorTitle = () => latest.current.errorNotificationTitle || trans('notifications::notifications.error');

    // Check if action has a form schema
    const hasFormSchema = !!(modalFormSchema && modalFormSchema.length > 0);

    // Check if action has an infolist schema
    const hasInfolistSchema = !!(modalInfolistSchema && modalInfolistSchema.length > 0);

    // Check if action has either form or infolist schema
    const hasFormOrInfolistSchema = hasFormSchema || hasInfolistSchema;

    // Modal width class based on modalWidth prop
    const widthMap: Record<string, string> = {
        sm: 'sm:max-w-md',
        md: 'sm:max-w-lg',
        lg: 'sm:max-w-2xl',
        xl: 'sm:max-w-3xl',
        '2xl': 'sm:max-w-4xl',
        '3xl': 'sm:max-w-5xl',
        '4xl': 'sm:max-w-6xl',
        '5xl': 'sm:max-w-7xl',
    };
    const modalWidthClass = widthMap[modalWidth || 'md'] || 'sm:max-w-lg';

    // Fill infolist schema with record data
    const filledInfolistSchema =
        !modalInfolistSchema || !externalFormData
            ? modalInfolistSchema || []
            : // Deep clone and fill values from externalFormData
              fillInfolistValues(modalInfolistSchema, externalFormData);

    // Map color to button variant (icon variant should always use ghost - no background)
    const buttonVariant: ButtonVariant =
        variant === 'icon' ? 'ghost' : variant === 'link' ? 'link' : isOutlined ? 'outline' : colorToVariant(color);

    // Button classes
    const buttonClass = (() => {
        const classes = ['cursor-pointer'];

        // Icon variant - no background, color on icon only
        if (variant === 'icon') {
            return cn(...classes); // Return with cursor-pointer, color will be on icon
        }

        // Add custom color classes for non-standard colors (non-icon variants)
        const custom = customColorClass(color);
        if (custom) {
            classes.push(custom);
        }

        return cn(...classes);
    })();

    // Icon color classes (for icon variant)
    const iconClass = (() => {
        const classes = ['size-4'];

        // Add order-last if icon position is after
        if (iconPosition === 'after') {
            classes.push('order-last');
        }

        // For icon variant, apply color to the icon
        if (variant === 'icon') {
            switch (color) {
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
    })();

    // Modal button variant (for confirmation modals - should reflect actual color, not ghost)
    const modalButtonVariant: ButtonVariant = isOutlined ? 'outline' : colorToVariant(color);

    // Modal button classes (for confirmation modals)
    const modalButtonClass = (() => {
        const classes: string[] = [];
        const custom = customColorClass(color);
        if (custom) {
            classes.push(custom);
        }
        return cn(...classes);
    })();

    // Modal icon background class
    const modalIconColorMap: Record<string, string> = {
        primary: 'bg-primary/10 text-primary',
        secondary: 'bg-secondary/10 text-secondary',
        danger: 'bg-destructive/10 text-destructive',
        destructive: 'bg-destructive/10 text-destructive',
        purple: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
        gray: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    };
    const modalIconClass = modalIconColorMap[modalIconColor || color || 'primary'] || modalIconColorMap.primary;

    // Get success message based on HTTP method
    const getSuccessMessage = (httpMethod: string): string => {
        const methodUpper = httpMethod.toUpperCase();
        if (methodUpper === 'POST') {
            return trans('actions::actions.messages.created');
        } else if (methodUpper === 'PUT' || methodUpper === 'PATCH') {
            return trans('actions::actions.messages.updated');
        } else if (methodUpper === 'DELETE') {
            return latest.current.isBulkAction
                ? trans('actions::actions.messages.bulk_deleted')
                : trans('actions::actions.messages.deleted');
        }
        return trans('actions::actions.messages.action_completed');
    };

    // Check if this action needs URL-based execution (URL + non-GET method)
    const hasUrlBasedAction = (): boolean => {
        const current = latest.current;
        return !!(current.url && current.method && current.method !== 'GET');
    };

    // Execute the action
    const executeAction = async () => {
        const current = latest.current;

        // If no closure action AND no URL-based action, just close modal
        if (!current.hasAction && !hasUrlBasedAction()) {
            setShowModal(false);
            return;
        }

        // Validate modal form if it exists
        const formRef = current.slideOver ? slideOverFormRef.current : modalFormRef.current;
        if (formRef && typeof formRef.validateForm === 'function') {
            if (!formRef.validateForm()) {
                return;
            }
        }

        // Validate parent form before executing action ONLY for submit-type actions
        // Cancel buttons (type='button', isSubmit=false) should NOT trigger form validation
        const shouldValidateForm = current.isSubmit === true || current.type === 'submit';
        if (shouldValidateForm && validateForm && !validateForm()) {
            return;
        }

        // Determine which type of action this is
        const isClosureAction = !!current.actionUrl; // Has actionUrl = closure-based action
        const executionUrl = current.actionUrl || current.url;

        // If action has URL, execute via backend
        if (executionUrl) {
            setIsLoading(true);

            try {
                // Collect form data - merge props.data with modal form data
                // props.data contains action-specific data (like importer class)
                // formData contains user input from modal form (like uploaded file)
                let actionData: Record<string, any> = {
                    ...(current.data || {}),
                    ...formDataRef.current,
                };

                // If we have getFormData callback, merge that too
                if (current.getFormData) {
                    actionData = {
                        ...actionData,
                        ...current.getFormData(),
                    };
                }

                // For record actions, ensure record context is always included
                // This is needed for edit/delete actions to know which record to operate on
                if (current.data && current.data.record) {
                    actionData = {
                        ...actionData,
                        record: current.data.record,
                        resourceName: current.data.resourceName,
                        model: current.data.model,
                    };
                }

                // If action has a token (closure action), send it with data wrapped
                // Otherwise (URL action), just send the form data directly
                const requestData = current.actionToken ? { token: current.actionToken, data: actionData } : actionData;

                // Use AJAX (fetch) if useAjax is true - this avoids page reload
                if (current.useAjax && !isClosureAction) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const httpMethod = (current.method || 'POST').toUpperCase();

                    const response = await fetch(executionUrl, {
                        method: httpMethod,
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: httpMethod !== 'GET' ? JSON.stringify(requestData) : undefined,
                    });

                    const result = await response.json();

                    if (response.ok) {
                        setShowModal(false);
                        updateFormData({});

                        // Show success notification based on method/action type
                        const successMessage = result.message || getSuccessMessage(httpMethod);
                        if (successMessage) {
                            notifyMessage({
                                title: trans('actions::actions.states.success'),
                                body: successMessage,
                                type: 'success',
                            });
                        }

                        // If this is a bulk action and should deselect records after completion
                        if (latest.current.isBulkAction && latest.current.deselectRecordsAfterCompletion === true) {
                            window.dispatchEvent(new CustomEvent('bulk-action-completed'));
                        }

                        // Emit action-complete event for parent components
                        latest.current.onActionComplete?.(result);
                    } else {
                        // Handle validation errors
                        if (result.errors && typeof result.errors === 'object') {
                            const firstError = firstErrorMessage(result.errors);
                            if (firstError !== undefined) {
                                notifyMessage({
                                    title: getTranslatedErrorTitle(),
                                    body: String(firstError),
                                    type: 'error',
                                });
                            }
                        } else if (result.message) {
                            notifyMessage({
                                title: getTranslatedErrorTitle(),
                                body: result.message,
                                type: 'error',
                            });
                        }
                    }

                    setIsLoading(false);
                    return;
                }

                // Use Inertia router (default behavior)
                const requestOptions: any = {
                    // Preserve state on errors (validation errors should keep form data)
                    // But on success, use the prop value
                    preserveState: (visitPage: any) => {
                        // If there are errors, always preserve state to keep form data
                        if (Object.keys(visitPage.props.errors || {}).length > 0) {
                            return true;
                        }
                        // Otherwise, use the configured value
                        return latest.current.preserveState;
                    },
                    preserveScroll: (visitPage: any) => {
                        // If there are errors, always preserve scroll
                        if (Object.keys(visitPage.props.errors || {}).length > 0) {
                            return true;
                        }
                        // Otherwise, use the configured value
                        return latest.current.preserveScroll;
                    },
                    onSuccess: (visitPage: any) => {
                        setShowModal(false);
                        updateFormData({});

                        // Check if the action returned a redirect URL (for closure actions)
                        if (isClosureAction && visitPage?.props?.actionUpdatedData?.redirect) {
                            const redirectUrl = visitPage.props.actionUpdatedData.redirect as string;
                            window.location.href = redirectUrl;
                            return;
                        }

                        // Close modal on success
                        setShowModal(false);
                        updateFormData({});

                        // If this is a bulk action and should deselect records after completion
                        if (latest.current.isBulkAction && latest.current.deselectRecordsAfterCompletion === true) {
                            window.dispatchEvent(new CustomEvent('bulk-action-completed'));
                        }

                        // Emit action-complete event for parent components
                        latest.current.onActionComplete?.(visitPage?.props?.actionUpdatedData);

                        // If the action updated form data (via Set), merge it back into parent form
                        // Only check for updated data if preserveState is true (otherwise page.props might not exist)
                        if (latest.current.preserveState && visitPage?.props) {
                            const updatedData = visitPage.props.actionUpdatedData as Record<string, any> | null;
                            if (updatedData && Object.keys(updatedData).length > 0) {
                                // Emit event to update parent form data
                                // This will be handled by Form / Schema
                                window.dispatchEvent(
                                    new CustomEvent('action-updated-data', {
                                        detail: updatedData,
                                    }),
                                );
                            }
                        }
                    },
                    onError: (errors: any) => {
                        console.error('Action execution failed:', errors);

                        // Show error notifications for each error
                        if (errors && typeof errors === 'object') {
                            // Show first error as notification (avoid spam if many errors)
                            const firstError = firstErrorMessage(errors);
                            if (firstError !== undefined) {
                                notifyMessage({
                                    title: getTranslatedErrorTitle(),
                                    body: String(firstError),
                                    type: 'error',
                                });
                            }
                        }
                    },
                    onFinish: () => {
                        setIsLoading(false);
                    },
                };

                // Use the appropriate HTTP method
                const httpMethod = (current.method || 'POST').toLowerCase() as 'get' | 'post' | 'put' | 'patch' | 'delete';

                (router as any)[httpMethod](executionUrl, requestData, requestOptions);
            } catch (error) {
                console.error('Action execution error:', error);
                setIsLoading(false);
            }
        } else {
            setShowModal(false);
            updateFormData({});
        }
    };

    // Handle click
    const handleClick = async (e: ReactMouseEvent<HTMLButtonElement>) => {
        const current = latest.current;

        // Show modal if requires confirmation OR has a form/infolist schema
        // This takes priority over direct navigation
        const hasModalContent =
            current.requiresConfirmation ||
            (current.modalFormSchema && current.modalFormSchema.length > 0) ||
            (current.modalInfolistSchema && current.modalInfolistSchema.length > 0);

        if (hasModalContent) {
            e.preventDefault();
            e.stopPropagation();

            // Initialize form data with external data if provided (for edit/view actions)
            if (current.externalFormData) {
                updateFormData({ ...current.externalFormData });
            } else {
                updateFormData({});
            }

            setShowModal(true);
            return;
        }

        // If it's a URL action without backend action AND not a special method, navigate
        // (DELETE, PUT, PATCH need to go through executeAction)
        if (current.url && !current.hasAction && (!current.method || current.method === 'GET')) {
            e.preventDefault();
            // Open in new tab if specified (e.g., for file downloads)
            if (current.openUrlInNewTab) {
                window.open(current.url, '_blank');
            } else {
                router.visit(current.url);
            }
            return;
        }

        // If it's a link variant with a URL, just navigate using Inertia
        if (current.variant === 'link' && current.url && !current.actionToken) {
            e.preventDefault();
            router.visit(current.url);
            return;
        }

        // Only prevent default for non-button elements or when we have an action
        if (current.hasAction || hasUrlBasedAction()) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Execute action directly
        await executeAction();
    };

    const Icon = icon ? resolveIcon(icon) : null;
    const ModalIcon = modalIcon ? resolveIcon(modalIcon) : null;

    // The Vue template binds href/target on the Button (rendered as attributes on the <button>)
    const linkAttributes: Record<string, any> = {
        href: url,
        target: openUrlInNewTab ? '_blank' : undefined,
    };

    const button = (
        <Button
            type={type}
            variant={buttonVariant}
            size={variant === 'icon' ? 'icon' : size}
            disabled={disabled || isLoading}
            className={cn(buttonClass, props.class, props.className)}
            onClick={handleClick}
            {...linkAttributes}
        >
            {isLoading ? <Spinner className="size-3" /> : Icon ? <Icon className={iconClass} /> : null}
            {label && variant !== 'icon' ? <span>{label}</span> : null}
        </Button>
    );

    const errors = (page.props as any).errors as Record<string, string | string[]>;

    const modalIconElement = modalIcon ? (
        <div className={`mx-auto mb-4 flex size-12 items-center justify-center rounded-full ${modalIconClass}`}>
            {ModalIcon ? <ModalIcon className="size-6" /> : null}
        </div>
    ) : null;

    const confirmButton =
        !isViewOnly && translatedConfirmLabel ? (
            <Button onClick={() => executeAction()} disabled={isLoading} variant={modalButtonVariant} className={modalButtonClass}>
                {isLoading ? <Spinner className="size-3 me-2" /> : null}
                {translatedConfirmLabel}
            </Button>
        ) : null;

    return (
        <>
            {tooltip ? (
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger asChild>{button}</TooltipTrigger>
                        <TooltipContent>{tooltip}</TooltipContent>
                    </Tooltip>
                </TooltipProvider>
            ) : (
                button
            )}

            {!slideOver ? (
                /* Action Confirmation Modal */
                <Dialog open={showModal} onOpenChange={setShowModal}>
                    <DialogContent className={modalWidthClass}>
                        <DialogHeader className={hasFormOrInfolistSchema ? 'text-start' : 'text-center sm:text-center'}>
                            {modalIconElement}
                            {modalHeading ? (
                                <DialogTitle className={hasFormOrInfolistSchema ? 'text-start' : 'text-center'}>{modalHeading}</DialogTitle>
                            ) : null}
                            {modalDescription ? (
                                <DialogDescription className={hasFormOrInfolistSchema ? 'text-start' : 'text-center'}>
                                    {modalDescription}
                                </DialogDescription>
                            ) : null}
                        </DialogHeader>

                        {modalInfolistSchema && modalInfolistSchema.length ? (
                            /* Modal Infolist Schema (for view only) */
                            <div className="-mx-6 px-6 py-4 max-h-[60vh] overflow-y-auto scroll-smooth overscroll-contain">
                                <InfoList schema={filledInfolistSchema} />
                            </div>
                        ) : modalFormSchema && modalFormSchema.length ? (
                            /* Modal Form Schema */
                            <div className="-mx-6 px-6 py-4 max-h-[60vh] overflow-y-auto scroll-smooth overscroll-contain">
                                <ErrorProvider errors={errors}>
                                    <Form
                                        ref={modalFormRef}
                                        schema={modalFormSchema}
                                        modelValue={formData}
                                        onUpdateModelValue={updateFormData}
                                        disabled={isViewOnly}
                                        formController={modalFormController}
                                    />
                                </ErrorProvider>
                            </div>
                        ) : null}

                        {/* Modal Content */}
                        {modalContent ? <div className="py-4 text-center" dangerouslySetInnerHTML={{ __html: modalContent }}></div> : null}

                        <DialogFooter
                            className={cn(hasFormOrInfolistSchema ? 'sm:justify-end rtl:justify-start' : 'sm:justify-center', 'gap-2')}
                        >
                            <Button variant="outline" onClick={() => setShowModal(false)}>
                                {translatedCancelLabel}
                            </Button>
                            {confirmButton}
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            ) : (
                /* Action Confirmation Slideover */
                <Sheet open={showModal} onOpenChange={setShowModal}>
                    <SheetContent>
                        <SheetHeader className={hasFormSchema ? 'text-start' : 'text-center'}>
                            {modalIconElement}
                            {modalHeading ? (
                                <SheetTitle className={hasFormSchema ? 'text-start' : 'text-center'}>{modalHeading}</SheetTitle>
                            ) : null}
                            {modalDescription ? (
                                <SheetDescription className={hasFormSchema ? 'text-start' : 'text-center'}>{modalDescription}</SheetDescription>
                            ) : null}
                        </SheetHeader>

                        {/* Form Schema */}
                        {modalFormSchema && modalFormSchema.length ? (
                            <div className="px-4 py-4 max-h-[70vh] overflow-y-auto scroll-smooth overscroll-contain">
                                <ErrorProvider errors={errors}>
                                    <Form
                                        ref={slideOverFormRef}
                                        schema={modalFormSchema}
                                        modelValue={formData}
                                        onUpdateModelValue={updateFormData}
                                        disabled={isViewOnly}
                                        formController={modalFormController}
                                    />
                                </ErrorProvider>
                            </div>
                        ) : null}

                        {/* Content */}
                        {modalContent ? (
                            <div className="px-4 py-4 text-center" dangerouslySetInnerHTML={{ __html: modalContent }}></div>
                        ) : null}

                        <SheetFooter className={cn(hasFormSchema ? 'sm:justify-end rtl:justify-start' : 'justify-center', 'gap-2')}>
                            <Button variant="outline" onClick={() => setShowModal(false)}>
                                {translatedCancelLabel}
                            </Button>
                            {confirmButton}
                        </SheetFooter>
                    </SheetContent>
                </Sheet>
            )}
        </>
    );
}
