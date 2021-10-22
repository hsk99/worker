<?php

namespace support\base;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $_instances = [];

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function get($name)
    {
        if (!isset($this->_instances[$name])) {
            if (!class_exists($name)) {
                throw new \Exception("Class '$name' not found");
            }
            $this->_instances[$name] = new $name();
        }
        return $this->_instances[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return \array_key_exists($name, $this->_instances);
    }

    /**
     * @param $name
     * @param array $constructor
     * @return mixed
     * @throws \Exception
     */
    public function make($name, array $constructor = [])
    {
        if (!class_exists($name)) {
            throw new \Exception("Class '$name' not found");
        }
        return new $name(...array_values($constructor));
    }
}
