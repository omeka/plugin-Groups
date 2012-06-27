<?php

class GroupMembership extends Omeka_Record
{
    public $id;
    public $group_id;
    public $user_id;
    public $is_admin;
    public $is_owner;
    public $is_pending;
    public $notify_member_joined;
    public $notify_item_new;
    public $notify_member_left;
    public $notify_item_deleted;

    protected $_related = array('Group'=>'getGroup', 'User'=>'getUser');
    
    public function unsetOptions()
    {
        //when the post array comes in from the form, unchecked items aren't in the data, so reset all to 0 so we can work from the post
        $this->is_admin =0;
        $this->notify_member_joined = 0;
        $this->notify_item_new = 0;
        $this->notify_member_left = 0;
        $this->notify_item_deleted = 0;
    }
    
    
    protected function beforeInsert()
    {
        $this->notify_member_joined = true;
        $this->notify_item_new = true;
        $this->notify_member_left = true;
        $this->notify_item_deleted = true;
    }
    
    public function getGroup()
    {
        return $this->getTable('Group')->find($this->group_id);
    }        
    
    public function getUser()
    {
        return $this->getTable('User')->find($this->user_id);
    }
    
    public function getConfirmation($role)
    {
        $params = array('group_id'=>$this->Group->id,
                        'membership_id'=>$this->id,
                        'type' => $role                
                );
        $confirmations = $this->getTable('GroupConfirmation')->findBy();
        if(empty($confirmations)) {
            return false;
        } else {
            return $confirmations[0];
        }
    }
}