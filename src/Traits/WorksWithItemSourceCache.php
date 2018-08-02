<?php

namespace DigitSoft\LaravelRbac\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait WorksWithItemSourceCache
 * @package DigitSoft\LaravelRbac\Traits
 */
trait WorksWithItemSourceCache
{
    /**
     * Get data from cache
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function getCache($key, $default = null)
    {
        if (is_array($key)) {
            $cacheKey = [];
            foreach ($key as $k) {
                $cacheKey[] = $this->getCacheKey($k);
            }
        } else {
            $cacheKey = $this->getCacheKey($key);
        }
        return Cache::get($cacheKey, $default);
    }

    /**
     * Set data to cache
     * @param string $key
     * @param mixed  $data
     */
    protected function setCache($key, $data)
    {
        $cacheKey = $this->getCacheKey($key);
        Cache::put($cacheKey, $data);
    }

    /**
     * Remove data from cache
     * @param string[]|string $keys
     */
    protected function forgetCache($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        foreach ($keys as $key) {
            $cacheKey = $this->getCacheKey($key);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get valid cache key
     * @param string $key
     * @return string
     */
    private function getCacheKey($key)
    {
        $className = get_called_class();
        return $className . ':' . $key;
    }
}