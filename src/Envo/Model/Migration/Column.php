<?php

namespace Envo\Model\Migration;

use Phinx\Db\Table\Column as PhinxColumn;

/**
 * Class Column
 * @package lib\Database
 * @method nullable()
 * @method default()
 * @method unsigned()
 * @method signed()
 */
class Column extends PhinxColumn
{

    /**
     * Dynamically set the value of an attribute.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
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