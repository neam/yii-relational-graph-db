<?php

/**
 * RelatedNodesBehavior
 *
 * @uses CActiveRecordBehavior
 * @license MIT
 * @author See https://github.com/neam/yii-relational-graph-db/graphs/contributors
 */
class RelatedNodesBehavior extends CActiveRecordBehavior
{

    /**
     * @param CActiveRecord $owner
     * @throws Exception
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!($owner instanceof CActiveRecord)) {
            throw new Exception('Owner must be a CActiveRecord class');
        }
    }

    public function beforeSave($event)
    {
        $this->initiateNode();
    }

    /**
     * Ensures node relation upon creation and updates
     */
    public function initiateNode()
    {

        if (is_null($this->owner->node_id)) {
            $node = new Node();
            $node->save();
            $this->owner->node_id = $node->id;
        }

    }

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

    /**
     * Handles node id arrays sent in post data with the relation name as array key. Example:
     *
     * array(3) {
     *   ["foo"]=>
     *   string(0) ""
     *   ["bar"]=>
     *   string(4) "barz"
     *   ["related"]=>
     *   array(3) {
     *     [0]=>
     *     string(1) "1"
     *     [1]=>
     *     string(1) "3"
     *     [2]=>
     *     string(1) "5"
     *   }
     * }
     *
     * @param $post array
     */
    public function handlePostedEdges($post)
    {

        $model = $this->owner;
        $node = $model->node();

        // array of models relation-names
        $relationNames = array_keys($model->relations());

        // Relations considered safe. Note: set the relation as safe in the model-rules (if it is not already)!
        $allowedRelations = array_filter(
        // attribute-names posted from form
            array_keys($post),
            // add to list if the attribute is a safe relation
            function ($attribute) use ($model, $relationNames) {
                return in_array($attribute, $relationNames) && $model->isAttributeSafe($attribute);
            }
        );

        foreach ($allowedRelations as $relationName) {
            $this->setOutEdges($post[$relationName], $relationName);
        }
    }


    /**
     * Sets the out-edges of the current model
     *
     * @param $relationName
     * @param array $futureOutEdgesNodeIds
     */
    public function setOutEdges(array $futureOutEdgesNodeIds, $relationName)
    {

        $model = $this->owner;
        $node = $model->node();

        $isFutureOutEdgesEmpty = empty($futureOutEdgesNodeIds);

        // Delete all outEdges for the relation if none is present in
        // form and the model has some outEdges (ie user removed edges)
        if ($isFutureOutEdgesEmpty && count($model->{$relationName}) > 0) {
            Edge::model()->deleteAllByAttributes(array(
                'from_node_id' => $node->id,
                'relation' => $relationName,
            ));
        }

        if ($isFutureOutEdgesEmpty) {
            $futureOutEdgesNodeIds = array();
        }

        $this->deleteEdgeDiff($futureOutEdgesNodeIds, $relationName);

        foreach ($futureOutEdgesNodeIds as $weight => $toNodeId) {
            $this->addEdge($node->id, $toNodeId, $relationName, $weight);
        }

    }

    /**
     * Deletes the edges which are present but not in future-edges
     *
     * @param $model ActiveRecord
     * @param $futureEdges array
     * @param $relationName string
     * @return int number of edges deleted
     */
    protected function deleteEdgeDiff(array $futureOutEdgesNodeIds, $relationName)
    {
        $model = $this->owner;

        // {1,2,3}
        $currentOutEdges = $model->getRelatedModelColumnValues($relationName, 'id');

        // {1,2,3} complement {2,3,4} = {1}
        $edgesToDelete = array_diff($currentOutEdges, $futureOutEdgesNodeIds);

        $criteria = new CDbCriteria();
        $criteria->addCondition('from_node_id = :from');
        $criteria->addCondition('relation = :relation');
        $criteria->addInCondition('to_node_id', $edgesToDelete);
        $criteria->params[':from'] = $model->node()->id;
        $criteria->params[':relation'] = $relationName;

        return Edge::model()->deleteAll($criteria);
    }

    protected function addEdge($fromNodeId, $toNodeId, $relationName, $weight = null)
    {
        $edge = Edge::model()->findByAttributes(array(
            'from_node_id' => $fromNodeId,
            'to_node_id' => $toNodeId,
            'relation' => $relationName,
        ));

        // Nothing has changed
        if ($edge !== null && $weight === null) {
            return;
        }

        if ($edge === null) {
            $edge = new Edge();
        }

        $edge->from_node_id = $fromNodeId;
        $edge->to_node_id = $toNodeId;
        $edge->relation = $relationName;

        if ($weight !== null) {
            $edge->weight = $weight;
        }

        if (!$edge->save()) {
            throw new SaveException($edge);
        }
        return true;
    }

    /**
     * @param string $relationName name of the relation
     * @param string $idColumn the column which values are collected from the related items
     * @return array
     */
    public function getRelatedModelColumnValues($relationName, $idColumn)
    {
        $ids = array();
        foreach ($this->owner->{$relationName} as $related) {
            $ids[] = $related->{$idColumn};
        }
        return $ids;
    }

}
