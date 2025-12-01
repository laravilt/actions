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
            $result = $action->execute($record, $data);

            return response()->json([
                'success' => true,
                'message' => 'Action executed successfully',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute a standalone action.
     */
    protected function executeStandaloneAction(array $payload, Request $request)
    {
        $actionId = $payload['action_id'];

        // Get the serializable closure from session
        $serializableClosure = session()->get("action.{$actionId}");

        if (!$serializableClosure) {
            return back()->withErrors(['action' => 'Action not found or expired']);
        }

        // Get the actual closure from SerializableClosure
        $actionClosure = $serializableClosure instanceof \Laravel\SerializableClosure\SerializableClosure
            ? $serializableClosure->getClosure()
            : $serializableClosure;

        if (!is_callable($actionClosure)) {
            return back()->withErrors(['action' => 'Invalid action']);
        }

        // Execute the action
        $data = $request->input('data', []);

        // Debug: Log the received data
        \Log::info('Action data received:', ['data' => $data]);

        // Use reflection to detect closure parameters and inject dependencies
        $reflection = new \ReflectionFunction($actionClosure);
        $parameters = $reflection->getParameters();
        $args = [];

        \Log::info('Action parameters:', [
            'count' => count($parameters),
            'params' => array_map(fn($p) => [
                'name' => $p->getName(),
                'type' => $p->getType()?->getName(),
            ], $parameters)
        ]);

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $typeName = $type && !$type->isBuiltin() ? $type->getName() : null;
            $paramName = $parameter->getName();

            // Inject Get utility
            if ($typeName === \Laravilt\Support\Utilities\Get::class) {
                \Log::info('Injecting Get utility with data:', $data);
                $args[] = new \Laravilt\Support\Utilities\Get($data);
            }
            // Inject Set utility
            elseif ($typeName === \Laravilt\Support\Utilities\Set::class) {
                \Log::info('Injecting Set utility with data reference');
                $args[] = new \Laravilt\Support\Utilities\Set($data);
            }
            // Parameter explicitly named 'data' always gets the data array
            elseif ($paramName === 'data') {
                $args[] = $data;
            }
            // Parameter explicitly named 'record' always gets null (for standalone actions)
            elseif ($paramName === 'record') {
                $args[] = null;
            }
            // First untyped parameter (no explicit name match) gets null as record
            elseif ($typeName === null && count($args) === 0) {
                $args[] = null;
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

        // Call the closure with resolved arguments
        \Log::info('Calling closure with args:', ['arg_count' => count($args)]);
        $actionClosure(...$args);
        \Log::info('After action execution, data is:', $data);

        // Flash updated data to session for the next request
        session()->flash('action_updated_data', $data);

        // Return back with 303 status to preserve scroll position
        return redirect()->back(303);
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
