<?php

trait GraphRelatableItemTrait
{

    public function relationalGraphDbRelation($relationName, $modelClass)
    {
        return array(
            $relationName => array(
                self::HAS_MANY,
                $modelClass,
                array('id' => 'node_id'),
                'through' => 'outNodes',
                'condition' => 'relation = :relation',
                'order' => 'outEdges.weight ASC',
                'params' => array(
                    ':relation' => $relationName,
                ),
            )
        );
    }

    public function relationalGraphDbRelatedOutNodes()
    {
        $relationName = 'related';
        return array(
            $relationName => array(
                CActiveRecord::HAS_MANY,
                'Node',
                array('id' => 'id'),
                'through' => 'outNodes',
                'condition' => 'relation=:relation',
                'order' => 'outEdges.weight ASC',
                'params' => array(
                    ':relation' => $relationName
                ),
            )
        );
    }


} 