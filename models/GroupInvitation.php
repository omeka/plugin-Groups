<?php

class GroupInvitation extends Omeka_Record
{
    public $id;
    public $user_id;
    public $sender_id;
    public $group_id;
    public $message;
    public $created;
    
    protected $_related = array('Group'=>'getGroup', 'User'=>'getUser', 'Sender'=>'getSender');
    
    public function getGroup()
    {
        return $this->getTable('Group')->find($this->group_id);
    }
    
    public function getUser()
    {
        return $this->getTable('User')->find($this->user_id);
    }
    
    public function getSender()
    {
        return $this->getTable('User')->find($this->sender_id);
    }    
        
    public function beforeInsert()
    {
        $this->created = Zend_Date::now()->toString(self::DATE_FORMAT);
    }
}