<?php

namespace Envo\Database\Migration;

/**
 * Class Column
 * @package lib\Database
 * @method nullable()
 * @method default()
 * @method unsigned()
 * @method signed()
 */
class Column extends \Phalcon\Db\Column
{
    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string $key
     * @param  mixed $value
     * @return self
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case 'nullable':
                $this->setNull(true);
                break;
            case 'default':
                $this->setDefault($value);
                break;
            case 'unsigned':
                $this->setSigned(FALSE);
                break;
            case 'signed':
                $this->setSigned(TRUE);
                break;
        }

        return $this;
    }

    /**
     * Handle dynamic calls to the container to set attributes.
     *
     * @param  string $method
     * @param  array $parameters
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $value = count($parameters) > 0 ? $parameters[0] : true;
        switch ($method) {
            case 'nullable':
                $this->setNull(true);
                break;
            case 'default':
                $this->setDefault($value);
                break;
            case 'unsigned':
                $this->setSigned(FALSE);
                break;
            case 'signed':
                $this->setSigned(TRUE);
                break;
        }

        return $this;
    }

}