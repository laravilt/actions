<?php

namespace Laravilt\Actions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;

class ActionController extends Controller
{
    /**
     * Execute an action.
     */
    public function execute(Request $request)
    {
        try {
            // Decrypt the action token
            $payload = Crypt::decrypt($request->input('token'));

            // Check if this is a standalone action (has action_id)
            if (isset($payload['action_id'])) {
                return $this->executeStandaloneAction($payload, $request);
            }

            // Component-based action
            $componentClass = $payload['component'];
            $componentId = $payload['id'];
            $actionName = $payload['action'];
            $panelId = $payload['panel'] ?? null;

            // Get the component instance
            $component = $this->resolveComponent($componentClass, $componentId, $panelId);

            if (! $component) {
                return response()->json([
                    'success' => false,
                    'message' => 'Component not found',
                ], 404);
            }

            // Get the action from the component
            $action = $this->getActionFromComponent($component, $actionName);

            if (! $action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action not found',
                ], 404);
            }

            // Check authorization
            $record = $componentId ? $this->resolveRecord($componentClass, $componentId) : null;

            if (! $action->canAuthorize($record)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Execute the action
            $data = $request->input('data', []);

            // Merge the action data into the request so that request()->validate() works
            $request->merge($data);

            $result = $action->execute($record, $data);

            // If the result is a redirect response, return it directly for Inertia to handle
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                \Log::info('ActionController: Returning redirect to: '.$result->getTargetUrl());

                return $result;
            }

            return response()->json([
                'success' => true,
                'message' => 'Action executed successfully',
                'result' => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let validation exceptions bubble up for proper Inertia handling
            throw $e;
        } catch (\Exception $e) {
            // For Inertia requests, redirect back with error
            if ($request->header('X-Inertia')) {
                return back()->withErrors([
                    'action' => 'Action execution failed: '.$e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Action execution failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute a standalone action.
     */
    protected function executeStandaloneAction(array $payload, Request $request)
    {
        \Log::info('ActionController::executeStandaloneAction called', [
            'session_id' => session()->getId(),
        ]);

        $actionId = $payload['action_id'];

        // Get the serializable closure from session
        $serializableClosure = session()->get("action.{$actionId}");

        if (! $serializableClosure) {
            return back()->withErrors(['action' => 'Action not found or expired']);
        }

        // Get the actual closure from SerializableClosure
        $actionClosure = $serializableClosure instanceof \Laravel\SerializableClosure\SerializableClosure
            ? $serializableClosure->getClosure()
            : $serializableClosure;

        if (! is_callable($actionClosure)) {
            return back()->withErrors(['action' => 'Invalid action']);
        }

        // Execute the action
        // Check if data is wrapped in 'data' key (old format) or sent directly (new format)
        $data = $request->has('data') ? $request->input('data', []) : $request->except(['_action_token', 'token', 'action']);

        // Ensure data is an array
        if (! is_array($data)) {
            $data = [];
        }

        // Use reflection to detect closure parameters and inject dependencies
        $reflection = new \ReflectionFunction($actionClosure);
        $parameters = $reflection->getParameters();
        $args = [];

        // Extract record from data if present (from table actions)
        // The record is sent as an array with 'id' and optionally 'model' class
        $record = null;
        if (isset($data['record']) && is_array($data['record'])) {
            $recordData = $data['record'];
            // Try to hydrate the record back to an Eloquent model
            if (isset($recordData['id']) && isset($data['model'])) {
                $modelClass = $data['model'];
                if (class_exists($modelClass)) {
                    $record = $modelClass::find($recordData['id']);
                }
            } elseif (isset($recordData['id']) && isset($recordData['_model'])) {
                // Alternative: model class stored in record itself
                $modelClass = $recordData['_model'];
                if (class_exists($modelClass)) {
                    $record = $modelClass::find($recordData['id']);
                }
            } else {
                // Fallback to raw array if we can't hydrate
                $record = $recordData;
            }
        }

        // Extract ids for bulk actions and hydrate to collection if model is specified
        $ids = null;
        $records = null;
        if (isset($data['ids']) && is_array($data['ids'])) {
            $ids = $data['ids'];
            // Try to hydrate to a collection of models
            if (isset($data['model']) && class_exists($data['model'])) {
                $modelClass = $data['model'];
                $records = $modelClass::whereIn('id', $ids)->get();
            }
        }

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $typeName = $type && ! $type->isBuiltin() ? $type->getName() : null;
            $paramName = $parameter->getName();

            // Inject Get utility
            if ($typeName === \Laravilt\Support\Utilities\Get::class) {
                $args[] = new \Laravilt\Support\Utilities\Get($data);
            }
            // Inject Set utility
            elseif ($typeName === \Laravilt\Support\Utilities\Set::class) {
                $args[] = new \Laravilt\Support\Utilities\Set($data);
            }
            // Parameter explicitly named 'data' always gets the data array
            elseif ($paramName === 'data') {
                $args[] = $data;
            }
            // Parameter explicitly named 'record' gets the extracted record
            elseif ($paramName === 'record') {
                $args[] = $record;
            }
            // Parameter explicitly named 'records' gets the hydrated collection (for bulk actions)
            elseif ($paramName === 'records' && $records !== null) {
                $args[] = $records;
            }
            // Parameter explicitly named 'ids' gets the ids array (for bulk actions)
            elseif ($paramName === 'ids' && $ids !== null) {
                $args[] = $ids;
            }
            // First untyped parameter - if we have records (bulk action), pass records collection;
            // if we have ids but no hydrated records, pass ids; otherwise pass record
            elseif ($typeName === null && count($args) === 0) {
                if ($records !== null) {
                    $args[] = $records;
                } elseif ($ids !== null) {
                    $args[] = $ids;
                } else {
                    $args[] = $record;
                }
            }
            // Second untyped parameter gets the data array
            elseif ($typeName === null) {
                $args[] = $data;
            }
            // Unknown typed parameter - try to resolve or pass null
            else {
                $args[] = null;
            }
        }

        // Merge the action data into the request so that request()->validate() works
        $request->merge($data);

        // Call the closure with resolved arguments
        // Any exceptions (including ValidationException) will bubble up to the main execute method
        $result = $actionClosure(...$args);

        // Get any notification that was flashed during action execution (BEFORE handling redirect)
        $notification = session()->pull('laravilt.notification');
        $notificationsArray = session()->pull('notifications', []);

        // Build the notifications array
        $notifications = [];
        if ($notification) {
            $notifications[] = $notification;
        }
        if (! empty($notificationsArray) && is_array($notificationsArray)) {
            $notifications = array_merge($notifications, $notificationsArray);
        }

        // If the result is a redirect response, attach notifications and return it
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            // Flash notifications for the redirect destination
            if (! empty($notifications)) {
                session()->flash('_laravilt_notifications', $notifications);
            }

            return $result;
        }

        // Determine what to flash to session
        // If result is an array, merge it with the original data (so both custom result and form data are available)
        // Otherwise just use the original data
        $actionData = is_array($result) && count($result) > 0
            ? array_merge($data, $result)
            : $data;

        // Note: $notifications was already pulled from session earlier (before handling redirect)

        // Flash data for the redirect
        session()->flash('action_updated_data', $actionData);
        session()->flash('_laravilt_notifications', $notifications);

        // Create redirect response
        $redirect = redirect()->back(303);

        // Add notifications to a cookie for frontend to read
        // Cookies persist across redirects, unlike response headers
        // The cookie is NOT encrypted (excluded in middleware) so JS can read it
        if (! empty($notifications)) {
            $redirect->cookie(
                'laravilt_notifications',
                base64_encode(json_encode($notifications)),
                1, // 1 minute expiry (short-lived)
                '/',
                null,
                false, // secure
                false, // httpOnly - must be false so JS can read it
                false, // raw
                'Lax'
            );
        }

        return $redirect;
    }

    /**
     * Resolve the component instance.
     */
    protected function resolveComponent(string $class, mixed $id, ?string $panelId): mixed
    {
        // For now, we'll just return null
        // This would be implemented based on your panel/resource architecture
        return null;
    }

    /**
     * Get action from component.
     */
    protected function getActionFromComponent(mixed $component, string $actionName): mixed
    {
        // Get actions from the component
        if (method_exists($component, 'getActions')) {
            $actions = $component->getActions();

            foreach ($actions as $action) {
                if ($action->getName() === $actionName) {
                    return $action;
                }
            }
        }

        return null;
    }

    /**
     * Resolve the record for the action.
     */
    protected function resolveRecord(string $componentClass, mixed $id): mixed
    {
        // This would be implemented based on your resource architecture
        return null;
    }
}
