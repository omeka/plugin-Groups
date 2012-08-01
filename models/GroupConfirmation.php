<?php

class GroupConfirmation extends Omeka_Record
{
    public $id;
    public $group_id;
    public $membership_id;
    public $type;
    
    protected $_related = array('Group'=>'getGroup', 'Membership'=>'getMembership');

    
    public function getGroup()
    {
        return $this->getTable('Group')->find($this->group_id);
    }
    
    public function getMembership()
    {
        return $this->getTable('GroupMembership')->find($this->membership_id);
    }    
    
}