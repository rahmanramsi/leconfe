<?php

namespace App\Managers;

use Closure;
use Illuminate\Support\Facades\Pipeline;
use Throwable;

class HookManager
{
    protected array $hooks = [];

    /**
     * Register Hook
     *
     */
    public function add(string $name, Closure|string|array $callback, int $sequence = 1): void
    {
        $this->hooks[$name][$sequence][] = &$callback;
    }


    /**
     * Call Hook
     */
    public function call(string $name, mixed $params = null): mixed
    {
        $hooks = $this->getHooks();
        if (!isset($hooks[$name])) {
            return false;
        }

        ksort($hooks[$name], SORT_NUMERIC);
		
        foreach ($hooks[$name] as $priority => $hookList) {
			$params = Pipeline::send($params)
				->through($hookList)
				->thenReturn();
        }

        return $params;
    }

    /**
     * Get All Available Hooks
     *
     * @return array
     * 
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Get Available Hooks by Name
     *
     * @param string $name
     * 
     * @return array
     * 
     */
    public function getHookByName(string $name): array
    {
        return $this->hooks[$name] ?? [];
    }

    /**
     * Clear hook by name
     *
     * @param string $name
     * 
     * @return [type]
     * 
     */
    public function clear(string $name): void
    {
        if (isset($this->hooks[$name])) {
            unset($this->hooks[$name]);
        }
    }

    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException(mixed $arguments, Throwable $e)
    {
        throw $e;
    }
}