<?php

namespace Laravilt\Actions;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Laravilt\Actions\Concerns\CanBeHidden;
use Laravilt\Actions\Concerns\HasColor;
use Laravilt\Actions\Concerns\HasIcon;
use Laravilt\Actions\Concerns\HasLabel;
use Laravilt\Actions\Concerns\HasModal;
use Laravilt\Actions\Concerns\HasUrl;

class Action implements Arrayable
{
    use CanBeHidden;
    use HasColor;
    use HasIcon;
    use HasLabel;
    use HasModal;
    use HasUrl;

    protected ?string $name = null;

    protected ?Closure $action = null;

    protected ?Closure $authorize = null;

    protected ?string $requiredPermission = null;

    protected ?string $requiredAbility = null;

    protected array $extraAttributes = [];

    protected ?string $size = null;

    protected bool $isDisabled = false;

    protected bool $isOutlined = false;

    protected ?string $variant = null;

    protected ?string $actionUrl = null;

    protected ?string $componentClass = null;

    protected mixed $componentId = null;

    protected ?string $panelId = null;

    protected ?string $tooltip = null;

    protected ?string $cachedActionToken = null;

    protected ?string $stableId = null;

    protected bool $preserveState = true;

    protected bool $preserveScroll = true;

    protected string $method = 'POST';

    protected bool $isSubmit = false;

    protected ?string $form = null;

    final public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->setUp();
    }

    /**
     * Configure the action.
     * Override this method in subclasses to set up defaults.
     */
    protected function setUp(): void
    {
        //
    }

    public static function make(?string $name = null): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function name(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function action(?Closure $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function authorize(?Closure $authorize): static
    {
        $this->authorize = $authorize;

        return $this;
    }

    /**
     * Set a required permission for this action.
     * Uses Spatie Permission to check if user has the permission.
     */
    public function can(string $permission): static
    {
        $this->requiredPermission = $permission;

        return $this;
    }

    /**
     * Set a required ability (Gate) for this action.
     * Uses Laravel Gates to check authorization.
     */
    public function ability(string $ability): static
    {
        $this->requiredAbility = $ability;

        return $this;
    }

    /**
     * Get the required permission.
     */
    public function getRequiredPermission(): ?string
    {
        return $this->requiredPermission;
    }

    /**
     * Get the required ability.
     */
    public function getRequiredAbility(): ?string
    {
        return $this->requiredAbility;
    }

    public function disabled(bool $condition = true): static
    {
        $this->isDisabled = $condition;

        return $this;
    }

    public function outlined(bool $condition = true): static
    {
        $this->isOutlined = $condition;

        return $this;
    }

    public function size(?string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function tooltip(?string $tooltip): static
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    public function preserveState(bool $preserve = true): static
    {
        $this->preserveState = $preserve;

        return $this;
    }

    public function preserveScroll(bool $preserve = true): static
    {
        $this->preserveScroll = $preserve;

        return $this;
    }

    /**
     * Set a stable ID for the action.
     * This ensures the action token remains consistent across requests.
     * Use for actions that need to persist (e.g., in ManageRecords).
     */
    public function stableId(string $id): static
    {
        $this->stableId = $id;

        return $this;
    }

    /**
     * Get the stable ID if set.
     */
    public function getStableId(): ?string
    {
        return $this->stableId;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set whether this action should submit a form.
     */
    public function submit(bool $condition = true): static
    {
        $this->isSubmit = $condition;

        return $this;
    }

    /**
     * Specify which form this action should submit.
     */
    public function submitForm(?string $form): static
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Check if this action should submit a form.
     */
    public function isSubmit(): bool
    {
        return $this->isSubmit;
    }

    /**
     * Get the form this action submits.
     */
    public function getSubmitForm(): ?string
    {
        return $this->form;
    }

    public function extraAttributes(array $attributes): static
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $attributes);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAction(): ?Closure
    {
        return $this->action;
    }

    public function canAuthorize(mixed $record = null): bool
    {
        $user = auth()->user();

        // Check super admin bypass
        if ($user && config('laravilt-users.super_admin.enabled', true)) {
            $superAdminRole = config('laravilt-users.super_admin.role', 'super_admin');
            if (method_exists($user, 'hasRole') && $user->hasRole($superAdminRole)) {
                return true;
            }
        }

        // Check required permission
        if ($this->requiredPermission !== null) {
            if (! $user) {
                return false;
            }

            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    if (! $user->hasPermissionTo($this->requiredPermission)) {
                        return false;
                    }
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    // Permission doesn't exist - allow access by default
                    // Run `php artisan laravilt:secure` to generate permissions
                }
            } else {
                try {
                    if (! \Illuminate\Support\Facades\Gate::allows($this->requiredPermission)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    // Gate not defined - allow access by default
                }
            }
        }

        // Check required ability (Gate)
        if ($this->requiredAbility !== null) {
            if ($record) {
                if (! \Illuminate\Support\Facades\Gate::allows($this->requiredAbility, $record)) {
                    return false;
                }
            } elseif (! \Illuminate\Support\Facades\Gate::allows($this->requiredAbility)) {
                return false;
            }
        }

        // Check custom authorize closure
        if ($this->authorize !== null) {
            return (bool) call_user_func($this->authorize, $record);
        }

        return true;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function isOutlined(): bool
    {
        return $this->isOutlined;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    /**
     * Get default label from name if label is not set.
     */
    protected function getDefaultLabel(): ?string
    {
        if (! $this->name) {
            return null;
        }

        // Convert snake_case or kebab-case to Title Case
        return str($this->name)
            ->replace(['-', '_'], ' ')
            ->title()
            ->toString();
    }

    /**
     * Set the action variant to 'link'.
     * Renders as a text link with underline.
     */
    public function link(): static
    {
        $this->variant = 'link';

        return $this;
    }

    /**
     * Set the action variant to 'button'.
     * Renders as a standard button.
     */
    public function button(): static
    {
        $this->variant = 'button';

        return $this;
    }

    /**
     * Set the action variant to 'icon'.
     * Renders as an icon-only button.
     */
    public function iconButton(): static
    {
        $this->variant = 'icon';

        return $this;
    }

    /**
     * Get the action variant.
     */
    public function getVariant(): ?string
    {
        return $this->variant;
    }

    /**
     * Set a custom action URL for this action.
     * If not set, the global action bridge will be used.
     */
    public function actionUrl(string $url): static
    {
        $this->actionUrl = $url;

        return $this;
    }

    /**
     * Get the action URL.
     */
    public function getActionUrl(): ?string
    {
        // If custom URL is set, use it
        if ($this->actionUrl) {
            return $this->actionUrl;
        }

        // Otherwise, use global action bridge
        return route('actions.execute');
    }

    /**
     * Set component metadata for generating action tokens.
     */
    public function component(string $class, mixed $id = null, ?string $panelId = null): static
    {
        $this->componentClass = $class;
        $this->componentId = $id;
        $this->panelId = $panelId;

        return $this;
    }

    /**
     * Clear component context to make this a standalone action.
     * Used when converting a component-based action to a standalone action with a closure.
     */
    public function clearComponent(): static
    {
        $this->componentClass = null;
        $this->componentId = null;
        $this->panelId = null;

        return $this;
    }

    /**
     * Generate an encrypted action token.
     */
    public function getActionToken(): string
    {
        // Auto-detect panel ID if not set
        $panelId = $this->panelId ?? $this->detectCurrentPanelId();

        // If action has a closure, always use standalone token (stored in session)
        // This ensures the closure is executed directly, not through component resolution
        if ($this->action) {
            return $this->getStandaloneActionToken($panelId);
        }

        // Component-based actions (no closure) use encrypted component metadata
        return \Illuminate\Support\Facades\Crypt::encrypt([
            'component' => $this->componentClass,
            'id' => $this->componentId,
            'action' => $this->getName(),
            'panel' => $panelId,
        ]);
    }

    /**
     * Detect the current panel ID from the panel registry.
     */
    protected function detectCurrentPanelId(): ?string
    {
        if (! class_exists(\Laravilt\Panel\PanelRegistry::class)) {
            return null;
        }

        try {
            $registry = app(\Laravilt\Panel\PanelRegistry::class);
            $panel = $registry->getCurrent();

            return $panel?->getId();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate token for standalone actions (without component context).
     */
    protected function getStandaloneActionToken(?string $panelId = null): string
    {
        // If we have a stable ID, use it for consistent tokens across requests
        if ($this->stableId !== null) {
            $actionId = 'action_'.$this->stableId;

            // Always update the closure in session (in case it was modified)
            $serializableClosure = new \Laravel\SerializableClosure\SerializableClosure($this->action);
            session()->put("action.{$actionId}", $serializableClosure);

            // Set the action URL to the execute route
            $this->actionUrl = route('actions.execute');

            // Return encrypted token with stable action_id
            return \Illuminate\Support\Facades\Crypt::encrypt([
                'action_id' => $actionId,
                'panel' => $panelId,
            ]);
        }

        // Return cached token if already generated (for non-stable actions)
        if ($this->cachedActionToken !== null) {
            return $this->cachedActionToken;
        }

        // Generate unique action ID using name and a unique identifier
        // This is used for one-time actions that don't need persistence
        $actionId = 'action_'.$this->getName().'_'.uniqid();

        // Wrap closure in SerializableClosure to allow session storage
        $serializableClosure = new \Laravel\SerializableClosure\SerializableClosure($this->action);

        // Store the serializable closure in session
        session()->put("action.{$actionId}", $serializableClosure);

        // Set the action URL to the execute route
        $this->actionUrl = route('actions.execute');

        // Cache and return encrypted token with action_id and panel
        $this->cachedActionToken = \Illuminate\Support\Facades\Crypt::encrypt([
            'action_id' => $actionId,
            'panel' => $panelId,
        ]);

        return $this->cachedActionToken;
    }

    /**
     * Serialize schema items to array, handling both objects with toLaraviltProps() and plain arrays.
     */
    protected function serializeSchema(array $schema): array
    {
        return array_map(function ($item) {
            if (is_object($item) && method_exists($item, 'toLaraviltProps')) {
                return $item->toLaraviltProps();
            }
            if (is_array($item)) {
                return $item;
            }

            return $item;
        }, $schema);
    }

    public function execute(mixed $record = null, array $data = []): mixed
    {
        if ($this->action === null) {
            return null;
        }

        return call_user_func($this->action, $record, $data);
    }

    /**
     * Get the component (Page) class this action belongs to.
     */
    public function getComponentClass(): ?string
    {
        return $this->componentClass;
    }

    /**
     * Resolve the action's configuration based on record context.
     * Override this in subclasses to auto-configure URLs/actions based on the record.
     */
    public function resolveRecordContext(mixed $record): static
    {
        // Base implementation does nothing - subclasses override this
        return $this;
    }

    public function toArray(): array
    {
        return $this->toArrayWithRecord(null);
    }

    /**
     * Convert action to array with record context for evaluating visibility.
     */
    public function toArrayWithRecord(mixed $record = null): array
    {
        $data = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'icon' => $this->getIcon(),
            'iconPosition' => $this->getIconPosition(),
            'url' => $this->getUrl(),
            'openUrlInNewTab' => $this->shouldOpenUrlInNewTab(),
            'requiresConfirmation' => $this->getRequiresConfirmation(),
            'hasModal' => $this->hasModal(),
            'modalHeading' => $this->getModalHeading(),
            'modalDescription' => $this->getModalDescription(),
            'modalSubmitActionLabel' => $this->getModalSubmitActionLabel(),
            'modalCancelActionLabel' => $this->getModalCancelActionLabel(),
            'modalIcon' => $this->getModalIcon(),
            'modalIconColor' => $this->getModalIconColor(),
            'modalFormSchema' => $this->serializeSchema($this->getModalFormSchema()),
            'modalFormController' => $this->componentClass, // For reactive fields in modal forms
            'modalFormData' => $this->getFilledFormData($record), // Pre-filled form data
            'modalInfolistSchema' => $this->serializeSchema($this->getModalInfolistSchema()),
            'modalWidth' => $this->getModalWidth(),
            'isViewOnly' => $this->getIsViewOnly(),
            'requiresPassword' => $this->getRequiresPassword(),
            'modalContent' => $this->getModalContent(),
            'slideOver' => $this->isSlideOver(),
            'isHidden' => $this->isHidden($record),
            'isDisabled' => $this->isDisabled(),
            'isOutlined' => $this->isOutlined(),
            'size' => $this->getSize(),
            'variant' => $this->getVariant(),
            'tooltip' => $this->getTooltip(),
            'extraAttributes' => $this->getExtraAttributes(),
            'hasAction' => $this->action !== null, // Only true if there's a backend action closure
            // Only set actionUrl for closure-based actions, not for URL-based actions
            'actionUrl' => ($this->action !== null) ? $this->getActionUrl() : null, // Action submission URL (for closures)
            'actionToken' => ($this->componentClass || $this->action) ? $this->getActionToken() : null, // Encrypted token
            'preserveState' => $this->preserveState,
            'preserveScroll' => $this->preserveScroll,
            'method' => $this->getMethod(),
            'isSubmit' => $this->isSubmit(),
            'submitForm' => $this->getSubmitForm(),
        ];

        // Filter out null/empty values to reduce payload size and avoid undefined behavior
        return array_filter($data, function ($value) {
            // Keep false, 0, and empty arrays, but remove null and empty strings
            return $value !== null && $value !== '';
        });
    }

    public function toLaraviltProps(): array
    {
        return $this->toArray();
    }

    public function toInertiaProps(): array
    {
        return $this->toArray();
    }
}
