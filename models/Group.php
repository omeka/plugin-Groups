<?php

class Group extends Omeka_Record implements Zend_Acl_Resource_Interface
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
        $this->_mixins[] = new Ownable($this);
    }

    public function addMember($user)
    {
        $rel = $this->newRelation($user, SIOC, 'has_member');
        $rel->user_id = $user->id;
        $rel->save();
    }

    public function removeMember($user)
    {
        $params = $this->buildProps($user, SIOC, 'has_member');
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->delete();
    }

    public function addPendingMember($user)
    {
        $rel = $this->newRelation($user, OMEKA, 'has_pending_member');
        $rel->user_id = $user->id;
        $rel->save();
    }

    public function approveMember($user)
    {

        $params = $this->buildProps($user, OMEKA, 'has_pending_member');
        $params['object_id'] = $user->id;
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->delete();
        $this->addMember($user);
    }

    public function addItem($item)
    {
        if(is_numeric($item)) {
            $item = get_db()->getTable('Item')->find($item);
        }
        $rel = $this->newRelation($item, DCTERMS, 'references');
        if($rel) {
            $rel->user_id = current_user()->id;
            $rel->save();
            return true;
        }
        return false;

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

    public function getItems()
    {
        $params = $this->buildParams('Item', DCTERMS, 'references');
        return get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params);
    }

    public function hasItem($item)
    {
        if(is_numeric($item)) {
            $itemId = $item;
        } else {
            $itemId = $item->id;
        }
        $params = $this->buildProps('Item', DCTERMS, 'references');
        $params['object_id'] = $itemId;
        return (bool) get_db()->getTable('RecordRelationsRelation')->count($params);
    }

    public function getItemCount()
    {
        $params = $this->buildProps('Item', DCTERMS, 'references');
        return get_db()->getTable('RecordRelationsRelation')->count($params);
    }

    public function getMembers()
    {
        $params = $this->buildProps('User', SIOC, 'has_member');
        return get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params);
    }

    public function getMemberCount()
    {
        $params = $this->buildProps('User', SIOC, 'has_member');
        return get_db()->getTable('RecordRelationsRelation')->count($params);
    }

    public function hasMember($user)
    {
        if(!$user->id) {
            return false;
        }
        $params = $this->buildProps('User', SIOC, 'has_member');
        $params['object_id'] = $user->id;
        $rels = get_db()->getTable('RecordRelationsRelation')->count($params);
        if($rels == 0) {
            return false;
        }
        return true;
    }

    public function hasPendingMember($user)
    {
        if(!$user->id) {
            return false;
        }
        $params = $this->buildProps('User', OMEKA, 'has_pending_member');
        $params['object_id'] = $user->id;
        $rels = get_db()->getTable('RecordRelationsRelation')->count($params);
        if($rels == 0) {
            return false;
        }
        return true;
    }

    public function memberRequests()
    {
        $params = $this->buildProps('User', OMEKA, 'has_pending_member');
        $users = get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params);
        return $users;
    }

    public function newRelation($object, $prefix, $localpart, $public = true)
    {
        $props = $this->buildProps($object, $prefix, $localpart);
        //first, see if the relation already exists
        $record = get_db()->getTable('RecordRelationsRelation')->findOne($props);
        if($record) {
            return false;
        }

        $rel = new RecordRelationsRelation();
        $props['public'] = $public;
        $rel->setProps($props);
        return $rel;
    }

    public function sendNewMemberEmail($user)
    {

    }

    public function sendMemberQuitEmail($user)
    {

    }

    public function sendNewItemEmail($item)
    {

    }

    protected function afterSaveForm($post)
    {
        //Add the tags after the form has been saved
        $current_user = Omeka_Context::getInstance()->getCurrentUser();
        $this->applyTagString($post['tags'], $current_user->Entity, true);

    }

    private function buildProps($record, $prefix, $localpart){

        $pred = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName($prefix, $localpart);
        $params = array();
        $params['subject_id'] = $this->id;
        $params['subject_record_type'] = 'Group';
        $params['property_id'] = $pred->id;
        //$record can be just a string class name if we're looking for many records
        if(is_string($record)) {
            $params['object_record_type'] = $record;
        } else {
            $params['object_id'] = $record->id;
            $params['object_record_type'] = get_class($record);
        }
        return $params;
    }

    public function getResourceId()
    {
        return 'Groups_Group';
    }
}