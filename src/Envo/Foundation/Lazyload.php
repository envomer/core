<?php

namespace Envo\Foundation;

class Lazyload
{
    /**
     * Loop an Phalon Simple array result
     * and lazyload the relation
     */
    public static function with($entries, $relationName)
    {
        if( ! $entries ) {
            return $entries;
        }
        
        $positions = array();
        $relation = null;
        foreach ($entries as $key => $entry) {
            if( ! $relation ) {
                // find the relation information (id, name)
                $relation = $entry->getRelations();
                if( ! isset($relation[$relationName]) ) {
                    throw new \Exception("Relationship does not exist", 500);
                }
                $relation = $relation[$relationName];
                $relationId = $relation->getFields();
            }
            
            $positions[$key] = $entry->$relationId;
        }
        
        // get the relation class and referenced fields
        $relationClass = $relation->getReferencedModel();
        $referencedFields = $relation->getReferencedFields();
        
        // make a query and get all related records
        $relatedModels = $relationClass::find(array(
            'conditions' => $referencedFields . ' IN ({relationField:array})',
            'bind' => array('relationField' => $positions)
        ));
        
        // reference the result by referenced field
        $sortedRelatedModels = array();
        foreach($relatedModels as $relatedModel) {
            $sortedRelatedModels[ $relatedModel->$referencedFields ] = $relatedModel;
        }
        
        // now combine the array with the found relations
        foreach($positions as $modelPosition => $relationPosition) {
            $entries[$modelPosition]->{'__' . $relationName} = $sortedRelatedModels[$relationPosition]; 
        }
        
        return $entries;
    }

    public static function fromResultset($a, $b)
    {
        return \Envo\Model\EagerLoad\Loader::fromResultset($a, $b);
    }
}