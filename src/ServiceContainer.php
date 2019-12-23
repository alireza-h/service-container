<?php

namespace Alireza\ServiceContainer;

use ArrayAccess;

final class ServiceContainer implements ArrayAccess
{
    /** @var self|null $instance */
    private static $instance = null;
    private $container = [];
    private $storage = [];
    private $factory = false;

    public function __construct(array $container = [])
    {
        $this->container = $container;
    }

    private function  __clone()
    {
    }

    public static function getInstance(array $container = [])
    {
        if(is_null(self::$instance) || empty(self::$instance->container)) {
            self::$instance = new self($container);
        }

        return self::$instance;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            return null;
        }

        if($this->factory) {
            $this->factory = false;
            return $this->isCallable($offset) ? $this->container[$offset]($this) : $this->container[$offset];
        }

        if($this->isCallable($offset)) {
            $service = isset($this->storage[$offset]) ? $this->storage[$offset] : $this->container[$offset]($this);
        } else {
            $service =  isset($this->storage[$offset]) ? $this->storage[$offset] : $this->container[$offset];
        }

        !isset($this->storage[$offset]) && $this->storage[$offset] = $service;

        return $service;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function factory()
    {
        $this->factory = true;
        return $this;
    }

    public function register(string $key, $value = null, $args = [])
    {
        if (is_object($value)) {
            $this->offsetSet($key, $value);
        } else if (class_exists($key)) {
            $this->offsetSet($key, function ($container) use ($key, $args) {
                if (!empty($args)) {
                    foreach ($args as $i => $arg) {
                        $args[$i] = $container[$arg];
                    }
                }
                return empty($args) ? new $key : new $key(...$args);
            });
        } else if (is_callable($value)) {
            $this->offsetSet($key, $value);
        } else {
            $this->offsetSet($key, $value);
        }

        return $this;
    }

    public function service($service = null, $factory = false)
    {
        $factory && $this->factory();

        return $service ? $this[$service] : $this;
    }

    private function isCallable($offset): bool
    {
        return method_exists($this->container[$offset], '__invoke');
    }
}