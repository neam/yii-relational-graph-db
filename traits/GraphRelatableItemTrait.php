<?php

/**
 * Trait GraphRelatableItemTrait
 *
 * @property Edge[] $outEdges
 * @property Node[] $outNodes
 * @property Edge[] $inEdges
 * @property Node[] $inNodes
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
        if ($this instanceof CActiveRecord) {
            return $this->ensureNode_yii();
        } else {
            return $this->ensureNode_propel();
        }
    }

    /**
     * Ensures node relation - Yii 1 AR
     * @return Node
     */
    public function ensureNode_yii()
    {

        if (is_null($this->node_id)) {
            $node = new Node();
            if (!$node->save()) {
                throw new SaveException($node);
            }
            $this->node_id = $node->id;
            $this->save();
            $this->refresh();
        }

        if (!($this->node instanceof Node)) {
            throw new CException(
                "Related node not available. \$this->node_id: {$this->node_id}, \$node: '" . print_r(
                    $this->node,
                    true
                ) . "'"
            );
        }

        return $this->node;

    }

    /**
     * Ensures node relation - Propel ORM
     * @return Node
     */
    public function ensureNode_propel()
    {

        $fakeNode = new Node();
        $fakeNode->id = -1;
        return $fakeNode;

        if (is_null($this->getNodeId())) {
            $node = new Node();
            if (!$node->save()) {
                throw new SaveException($node);
            }
            $this->node_id = $node->getId();
            $this->save();
            $this->refresh();
        }

        /*
        if (!($this->getNode() instanceof Node)) {
            throw new CException(
                "Related node not available. \$this->node_id: {$this->node_id}, \$node: '" . print_r(
                    $this->node,
                    true
                ) . "'"
            );
        }
        */

        return $this->getNode();

    }

}
