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

    /**
     * @param $relationName
     * @param $modelClass Either a model class name or '*', indicating that any item type may be related
     * @param string $refFk
     * @return array
     */
    public function relationalGraphDbRelation($relationName, $modelClass, $refFk = 'node_id')
    {
        // If we have no model class constraint, we need to relate only to the related items' nodes
        if ($modelClass === '*' || $modelClass === 'Node') {
            $modelClass = 'Node';
            $refFk = 'id';
        }
        return array(
            $relationName => array(
                CActiveRecord::HAS_MANY,
                $modelClass,
                array('id' => $refFk),
                'through' => 'outNodes',
                'condition' => 'relation = :relation',
                'order' => 'outEdges.weight ASC',
                'params' => array(
                    ':relation' => $relationName,
                ),
            )
        );
    }

    /**
     * Ensures node relation
     * @return Node
     */
    public function ensureNode()
    {

        if (is_null($this->node_id)) {
            $node = new Node();
            $node->save();
            $this->node_id = $node->id;
            $this->save();
            $this->refresh();
        }

        return $this->node;

    }

}
