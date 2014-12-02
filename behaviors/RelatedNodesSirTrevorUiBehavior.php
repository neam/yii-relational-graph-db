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
        ));

    protected $_st_attributes = array();

    public function init()
    {
        foreach ($this->attributes as $attribute => $config) {
            $this->_st_attributes[$attribute] = null;
        }
        return parent::init();
    }

    /**
     * Expose temporary sir-trevor ui attributes as readable
     */
    public function canGetProperty($name)
    {
        return $this->handlesProperty($name, "get");
    }

    /**
     * Expose temporary sir-trevor ui attributes as writable
     */
    public function canSetProperty($name)
    {
        return $this->handlesProperty($name, "set");
    }

    /**
     *
     * @param string $name
     * @return bool
     */
    protected function handlesProperty($name, $check = "get")
    {
        if (in_array(str_replace("sir_trevor_ui_", "", $name), array_keys($this->attributes))) {
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
            $validators->add(CValidator::createValidator('safe', $owner, "sir_trevor_ui_" . $attribute, array()));
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

        return '{
    "data": [
        {
            "type": "composition",
            "data": {
                "node_id": 1205,
                "item_type": "composition"
            }
        },
        {
            "type": "composition",
            "data": {
                "node_id": 1239,
                "item_type": "composition"
            }
        }
    ]
}';

        return "{}";

    }

    /**
     * Make temporary sir-trevor ui attributes writable
     */
    public function __set($name, $value)
    {

        if (!$this->handlesProperty($name)) {
            return parent::__set($name, $value);
        }

        var_dump(__LINE__, $name, $value);
        die();

        //

    }


    public function beforeSave($event)
    {

        //die("beforeSave asdfasfasfd");
    }

    /**
     * Reconstruct the
     */
    public function afterFind($event)
    {
        return;
        foreach ($this->attributes as $attribute => $config) {
            $this->_st_attributes[$attribute] = json_encode($this->toData($config));
        }
        //var_dump($event);
        //die("afterFind asdfasfasfd");
    }

    protected function toData($config)
    {
        $relation = $config["relation"];
        $ordered = $config["ordered"];
        foreach ($this->owner->{$relation} as $relatedItem) {

        }
        return array("foo" => "bar");
    }

    protected function setData($stJson)
    {
        /*
        $relation = $config["relation"];
        $ordered = $config["ordered"];
        foreach ($this->owner->{$relation}) {

        }
        */
        // TODO - Save edges...? or more general hmm. $stJson
    }

    protected function getRelatedItems()
    {
        die("afterFind asdfasfasfd");
    }

}