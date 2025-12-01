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

    final public function __construct(?string $name = null)
    {
        $this->name = $name;
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
        if ($this->authorize === null) {
            return true;
        }

        return (bool) call_user_func($this->authorize, $record);
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
        if (!$this->name) {
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
     * Generate an encrypted action token.
     */
    public function getActionToken(): string
    {
        // Check if this is a standalone action (has closure but no component)
        if ($this->action && !$this->componentClass) {
            return $this->getStandaloneActionToken();
        }

        return \Illuminate\Support\Facades\Crypt::encrypt([
            'component' => $this->componentClass,
            'id' => $this->componentId,
            'action' => $this->getName(),
            'panel' => $this->panelId,
        ]);
    }

    /**
     * Generate token for standalone actions (without component context).
     */
    protected function getStandaloneActionToken(): string
    {
        // Return cached token if already generated
        if ($this->cachedActionToken !== null) {
            return $this->cachedActionToken;
        }

        // Generate action ID based on action name for consistency
        $actionId = 'action_' . $this->getName();

        // Wrap closure in SerializableClosure to allow session storage
        $serializableClosure = new \Laravel\SerializableClosure\SerializableClosure($this->action);

        // Store the serializable closure in session
        session()->put("action.{$actionId}", $serializableClosure);

        // Set the action URL to the execute route
        $this->actionUrl = route('actions.execute');

        // Cache and return encrypted token with action_id
        $this->cachedActionToken = \Illuminate\Support\Facades\Crypt::encrypt([
            'action_id' => $actionId,
        ]);

        return $this->cachedActionToken;
    }

    public function execute(mixed $record = null, array $data = []): mixed
    {
        if ($this->action === null) {
            return null;
        }

        return call_user_func($this->action, $record, $data);
    }

    public function toArray(): array
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
            'modalHeading' => $this->getModalHeading(),
            'modalDescription' => $this->getModalDescription(),
            'modalSubmitActionLabel' => $this->getModalSubmitActionLabel(),
            'modalCancelActionLabel' => $this->getModalCancelActionLabel(),
            'modalIcon' => $this->getModalIcon(),
            'modalIconColor' => $this->getModalIconColor(),
            'modalFormSchema' => collect($this->getModalFormSchema())->map->toLaraviltProps()->toArray(),
            'requiresPassword' => $this->getRequiresPassword(),
            'modalContent' => $this->getModalContent(),
            'slideOver' => $this->isSlideOver(),
            'isHidden' => $this->isHidden(),
            'isDisabled' => $this->isDisabled(),
            'isOutlined' => $this->isOutlined(),
            'size' => $this->getSize(),
            'variant' => $this->getVariant(),
            'tooltip' => $this->getTooltip(),
            'extraAttributes' => $this->getExtraAttributes(),
            'hasAction' => $this->action !== null, // Indicates if action has a closure/method
            'actionUrl' => $this->getActionUrl(), // Action submission URL
            'actionToken' => ($this->componentClass || $this->action) ? $this->getActionToken() : null, // Encrypted token
        ];

        // Filter out null/empty values to reduce payload size and avoid undefined behavior
        return array_filter($data, function ($value) {
            // Keep false, 0, and empty arrays, but remove null and empty strings
            return $value !== null && $value !== '';
        });
    }
}
