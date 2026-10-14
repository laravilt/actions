<?php

namespace Laravilt\Actions\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Resolves the panel resource (and Laravel policy) that guards a model so that
 * actions can honour `Resource::canDelete()` & friends, both for visibility and
 * on the server when the action is executed.
 */
class ResourceAuthorizer
{
    /**
     * Find the resource class registered for a model in the current panel
     * (falling back to every registered panel).
     *
     * @return class-string|null
     */
    public static function findResourceForModel(?string $model): ?string
    {
        if (! $model) {
            return null;
        }

        $registryClass = 'Laravilt\Panel\PanelRegistry';

        if (! class_exists($registryClass) || ! app()->bound($registryClass)) {
            return null;
        }

        try {
            $registry = app($registryClass);
            $panels = [];

            if ($current = $registry->getCurrent()) {
                $panels[] = $current;
            }

            foreach ($registry->all() as $panel) {
                $panels[] = $panel;
            }

            foreach ($panels as $panel) {
                foreach ($panel->getResources() as $resource) {
                    if (method_exists($resource, 'getModel') && $resource::getModel() === $model) {
                        return $resource;
                    }
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Check an ability (delete, restore, forceDelete, ...) for a model/record.
     *
     * Returns null when neither a resource nor a policy governs the model, so the
     * caller can apply its own fallback.
     */
    public static function check(string $ability, ?string $model, mixed $record = null, ?string $resource = null): ?bool
    {
        if ($model === null && $record instanceof Model) {
            $model = $record::class;
        }

        $resource ??= static::findResourceForModel($model);
        $method = 'can'.ucfirst($ability);

        if ($resource && class_exists($resource) && method_exists($resource, $method)) {
            return (bool) $resource::$method($record instanceof Model ? $record : null);
        }

        if ($model && class_exists($model) && auth()->user() && Gate::getPolicyFor($record instanceof Model ? $record : $model) !== null) {
            $policy = Gate::getPolicyFor($record instanceof Model ? $record : $model);

            if ($record instanceof Model) {
                return Gate::allows($ability, $record);
            }

            if (method_exists($policy, $ability.'Any')) {
                return Gate::allows($ability.'Any', $model);
            }

            return Gate::allows($ability, new $model);
        }

        return null;
    }

    /**
     * Abort with 403 unless the ability is granted on the model and on every given record id.
     *
     * @param  array<int|string>  $ids
     */
    public static function authorizeBulk(string $ability, string $model, array $ids, bool $withTrashed = false, ?string $resource = null): void
    {
        if (static::check($ability, $model, null, $resource) === false) {
            abort(403, __('actions::actions.messages.unauthorized'));
        }

        if ($ids === []) {
            return;
        }

        $query = $model::query();

        if ($withTrashed && method_exists($query, 'withTrashed')) {
            $query->withTrashed();
        }

        foreach ($query->whereIn((new $model)->getKeyName(), $ids)->get() as $record) {
            if (static::check($ability, $model, $record, $resource) === false) {
                abort(403, __('actions::actions.messages.unauthorized'));
            }
        }
    }
}
