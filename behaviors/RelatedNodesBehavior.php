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

    /**
     * @param string $relation name of the relation
     * @param string $idColumn the column which values are collected from the related items
     * @return array
     */
    public function getRelatedModelColumnValues($relation, $idColumn)
    {
        $ids = array();
        foreach ($this->owner->{$relation} as $related) {
            $ids[] = $related->{$idColumn};
        }
        return $ids;
    }

}
