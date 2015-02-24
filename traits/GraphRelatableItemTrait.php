<?php

/**
 * Trait GraphRelatableItemTrait
 *
 * @license BSD-3-Clause
 * @author See https://github.com/neam/yii-relational-graph-db/graphs/contributors
 */
trait GraphRelatableItemTrait
{

    public function graphRelatableItemBaseRelations()
    {
        return array(
            'outEdges' => array(CActiveRecord::HAS_MANY, 'Edge', array('id' => 'from_node_id'), 'through' => 'node'),
            'outNodes' => array(CActiveRecord::HAS_MANY, 'Node', array('to_node_id' => 'id'), 'through' => 'outEdges'),
            'inEdges' => array(CActiveRecord::HAS_MANY, 'Edge', array('id' => 'to_node_id'), 'through' => 'node'),
            'inNodes' => array(CActiveRecord::HAS_MANY, 'Node', array('from_node_id' => 'id'), 'through' => 'inEdges'),
        );
    }

    public function relationalGraphDbRelation($relationName, $modelClass)
    {
        return array(
            $relationName => array(
                CActiveRecord::HAS_MANY,
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