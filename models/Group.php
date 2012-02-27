<?php

class Group extends Omeka_Record
{
    public $id;
    public $title;
    public $description;
    public $visibility;
    public $owner_id;

    protected $_related = array('Tags' => 'getTags');

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Taggable($this);
    }

    public function addMember($user)
    {
        //check if current user has permission to add a member. only the owner can
        //use an Acl_Assertion class?
        $rel = $this->newRelation($user, SIOC, 'has_member');
    }

    public function removeMember($user)
    {
        $params = $this->buildProps($user, SIOC, 'has_member');
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->delete();
    }

    public function addItem($item)
    {
        $rel = $this->newRelation($item, DCTERMS, 'references');
    }

    public function removeItem($item)
    {
        $params = $this->buildProps($item, DCTERMS, 'references');
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->delete();
    }

    public function setRelationPublic($record, $prefix, $localpart, $public)
    {
        $params = $this->buildProps($record, $prefix, $localpart);
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->public = $public;
        $rel->save();
    }

    public function newRelation($object, $prefix, $localpart, $public = true)
    {
        $rel = new RecordRelationsRelation();
        $props = $this->buildProps($object, $prefix, $localpart);
        $props['public'] = $public;
        $rel->setProps($props);
        $rel->save();

        return $rel;
    }

    protected function afterSaveForm($post)
    {
        //Add the tags after the form has been saved
        $current_user = Omeka_Context::getInstance()->getCurrentUser();
        $this->applyTagString($post['tags'], $current_user->Entity, true);

    }

    private function buildProps($record, $prefix, $localpart){

        $predId = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName($prefix, $localpart);
        $params = array();
        $params['subject_id'] = $this->id;
        $params['subject_record_type'] = 'Group';
        $params['property_id'] = $predId;
        $params['object_id'] = $record->id;
        $params['object_record_type'] = get_class($record);
        return $params;
    }
}