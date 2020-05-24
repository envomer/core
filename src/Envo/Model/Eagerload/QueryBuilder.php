<?php 

namespace Envo\Model\Eagerload;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Query\BuilderInterface;

final class QueryBuilder extends Builder
{
    const E_NOT_ALLOWED_METHOD_CALL = 'When eager loading relations queries must return full entities';
    
    public function distinct($distinct): BuilderInterface
    {
        throw new \LogicException(static::E_NOT_ALLOWED_METHOD_CALL);
    }
    
    public function where(string $conditions, array $bindParams = array(), array $bindTypes = array()): BuilderInterface
    {
        $currentConditions = $this->conditions;
        /**
         * Nest the condition to current ones or set as unique
         */
        if ($currentConditions) {
            $conditions = "(" . $currentConditions . ") AND (" . $conditions . ")";
        }
        return parent::where($conditions, $bindParams, $bindTypes);
    }
}