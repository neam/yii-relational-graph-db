<?php

/**
 * Behavior that eases setting up a ordered related items UI using sir trevor
 *
 * Adds virtual attributes - each of the configured attributes prefixed with "sir_trevor_ui_"
 * to use together with CompositionWidget (TODO: Rename to SirTrevorWithCustomBlocksWidget?)
 *
 * Class RelatedNodesSirTrevorUiBehavior
 */
class RelatedNodesSirTrevorUiBehavior extends CActiveRecordBehavior
{

    public $attributes = array(
        "related" => array(
            "ordered" => true,
            "relation" => "related",
        )
    );

    protected $_toSave = array();

    public function init()
    {
        foreach ($this->attributes as $attribute => $config) {
            $this->_toSave[$attribute] = null;
        }
        return parent::init();
    }

    public function virtualToActualAttribute($name)
    {
        return str_replace("sir_trevor_ui_", "", $name);
    }

    public function actualToVirtualAttribute($attribute)
    {
        return "sir_trevor_ui_" . $attribute;
    }

    /**
     * Expose temporary sir-trevor ui attributes as readable
     */
    public function canGetProperty($name)
    {
        return $this->handlesProperty($name);
    }

    /**
     * Expose temporary sir-trevor ui attributes as writable
     */
    public function canSetProperty($name)
    {
        return $this->handlesProperty($name);
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    protected function handlesProperty($name)
    {
        if (in_array($this->virtualToActualAttribute($name), array_keys($this->attributes))) {
            return true;
        }
    }

    /**
     * Mark the sir-trevor ui attributes as safe, so that forms that rely
     * on setting attributes from post values works without modification.
     *
     * @param CActiveRecord $owner
     * @throws Exception
     */
    public function attach($owner)
    {
        parent::attach($owner);
        if (!($owner instanceof CActiveRecord)) {
            throw new Exception('Owner must be a CActiveRecord class');
        }

        $validators = $owner->getValidatorList();

        foreach ($this->attributes as $attribute => $config) {
            $validators->add(CValidator::createValidator('safe', $owner, $this->actualTovirtualAttribute($attribute), array()));
        }

    }

    /**
     * Make temporary sir-trevor ui attributes readable
     */
    public function __get($name)
    {

        if (!$this->handlesProperty($name)) {
            return parent::__get($name);
        }

        $sirTrevorData = array("data" => array());

        $relationAttribute = $this->virtualToActualAttribute($name);

        if (count($this->owner->$relationAttribute) > 0) {

            $qaModels = DataModel::qaModels();

            foreach ($this->owner->$relationAttribute as $node) {
                $to_node_id = $node->id;
                $item = $node->item();
                $table = $qaModels[get_class($item)];
                $sirTrevorData["data"][] = array(
                    "type" => "$table",
                    "data" => array(
                        "node_id" => $to_node_id,
                        "item_type" => "$table",
                    ),
                );
            }

        }

        $json = json_encode($sirTrevorData);
        return $json;

    }

    /**
     * Make temporary sir-trevor ui attributes writable
     */
    public function __set($name, $value)
    {
        if (!$this->handlesProperty($name)) {
            return parent::__set($name, $value);
        }
        $this->_toSave[$name] = $value;
    }

    public function beforeSave($event)
    {

        foreach ($this->_toSave as $name => $value) {

            if (empty($value)) {
                Yii::log("$name was empty so no attempt to save related items will be made", 'info', __METHOD__);
                continue;
            }

            $futureOutEdgesNodeIds = array();
            $sirTrevorData = json_decode($value);

            foreach ($sirTrevorData->data as $block) {
                $futureOutEdgesNodeIds[] = $block->data->node_id;
            }

            $this->owner->setOutEdges($futureOutEdgesNodeIds, $this->virtualToActualAttribute($name));

            unset($this->_toSave[$name]);
        }

        return true;

    }

}