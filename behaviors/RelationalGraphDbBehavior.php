<?php

/**
 * RelationalGraphDbBehavior
 *
 * @uses CActiveRecordBehavior
 * @license MIT
 * @author See https://github.com/neam/yii-relational-graph-db/graphs/contributors
 */
class RelationalGraphDbBehavior extends CActiveRecordBehavior
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

}
