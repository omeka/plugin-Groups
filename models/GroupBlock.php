<?php

class GroupBlock extends Omeka_Record
{
    public $blocked_id;
    public $blocker_id;
    public $blocked_type;
    public $blocker_type;
    
    protected $_related = array('Blocked'=>'getBlocked', 'Blocker'=>'getBlocker');
    
    
    public function getBlocked()
    {
        return $this->getDb()->getTable($this->blocked_type)->find($this->blocked_id);
    }
    
    public function getBlocker()
    {
        return $this->getDb()->getTable($this->blocker_type)->find($this->blocker_id);
    }
}