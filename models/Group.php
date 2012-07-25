<?php

class Group extends Omeka_Record implements Zend_Acl_Resource_Interface
{
    public $id;
    public $title;
    public $description;
    public $visibility;    

    protected $_related = array('Tags' => 'getTags');

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Taggable($this);
        $this->_mixins[] = new Ownable($this);
    }

    public function addMember($user, $pending = 0, $role = null)
    {
        $membership = new GroupMembership;
        $membership->unsetOptions();
        $membership->user_id = $user->id;
        $membership->group_id = $this->id;
        $membership->is_pending = $pending;
        $membership->is_admin = 0;
        $membership->is_owner = 0;
        if($role) {
            $membership->$role = 1;
        } 
        $membership->save();
    }

    public function removeMember($user)
    {
        if($user instanceof User) {
            $membership = $this->findMembership($user);
        } elseif($user instanceof GroupMembership) {
            $membership = $user;
        }        
        $membership->delete();
    }

    public function approveMember($user)
    {
        if($user instanceof User) {
            $membership = $this->findMembership($user);
        } elseif($user instanceof GroupMembership) {
            $membership = $user;
        }
        
        $membership->is_pending = false;
        $membership->save();
    }

    public function denyMembership($user)
    {
        if($user instanceof User) {
            $membership = $this->findMembership($user);
        } elseif($user instanceof GroupMembership) {
            $membership = $user;
        }
        
        $membership->delete();
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

    public function getMembers($sort = array())
    {
        return get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id, 'is_pending'=>false), $sort);
    }
    
    public function getMemberCount()
    {
        return get_db()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'is_pending'=>false));        
    }

    public function hasMember($user)
    {
        if(!$user->id) {
            return false;
        }
        $count = get_db()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'is_pending'=>0, 'user_id'=>$user->id));
        if($count == 0) {
            return false;
        }
        return true;
    }

    public function hasPendingMember($user)
    {
        if(!$user->id) {
            return false;
        }
        $count = get_db()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'is_pending'=>true, 'user_id'=>$user->id));
        if($count == 0) {
            return false;
        }
        return true;
    }

    public function memberRequests()
    {
        return get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id, 'is_pending'=>true));
    }
    

    private function newRelation($object, $prefix, $localpart, $public = true)
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

    public function sendPendingMemberEmail($user, $to=null)
    {
        if($to) {                        
            $body = "User {$user->name} has requested membership in {$this->title} group on Omeka Commons. You can log into Omeka Commons and manage memberships here: ";
            $body .= WEB_ROOT . "/groups/show/" . $this->id;
            $email = $this->getEmailBase($to);
            $email->setSubject("A new member wants to join {$this->title} on Omeka Commons");
            $email->setBodyHtml($body); 
            try {
                $email->send();
            } catch(Exception $e) {
                _log($e);
            }
            
        }
    }
    
    public function sendNewMemberEmail($user, $to=null)
    {
        if($to) {
            $body = "A new member {$user->name} has joined the {$this->title} group on Omeka Commons.";
            $body .= WEB_ROOT . "/groups/show/" . $this->id;
            $email = $this->getEmailBase($to);
            $email->setSubject("A new member has joined {$this->title} on Omeka Commons");
            $email->setBodyHtml($body);        
            try {
                $email->send();
            } catch(Exception $e) {
                _log($e);
            }
        }
    }

    public function sendMemberLeftEmail($user, $to=null)
    {
        if($to) {
            $body = "{$user->name} has left the {$this->title} group on Omeka Commons. ";
            $body .= WEB_ROOT . "/groups/show/" . $this->id;
            $email = $this->getEmailBase($to);
            $email->setSubject("A member has left {$this->title} on Omeka Commons");
            $email->setBodyHtml($body);
            try {
                $email->send();
            } catch(Exception $e) {
                _log($e);
            }
        }
    }

    public function sendNewItemEmail($item, $to = null)
    {
        if($to) {
            $body = "A new item been added to the {$this->title} group on Omeka Commons. ";
            $body .= item('Dublin Core', 'Title', array(), $item);
            $body .= WEB_ROOT . "/groups/show/" . $this->id;
            $email = $this->getEmailBase($to);
            $email->setSubject("A new item has been added to {$this->title} on Omeka Commons");
            $email->setBodyHtml($body);
            try {
                $email->send();
            } catch(Exception $e) {
                _log($e);
            }
        }        
    }

    public function sendMemberApprovedEmail($user)
    {
        $body = "Your request to join {$this->title} on Omeka Commons has been approved. ";
        $body .= "<a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a>";
        $email = $this->getEmailBase(array($user));
        $email->setSubject("Your request to join {$this->title} on Omeka Commons has been approved!");
        $email->setBodyHtml($body);
        try {
            $email->send();
        } catch(Exception $e) {
            _log($e);
        }        
    }

    public function sendMemberDeniedEmail($user) 
    {
        
        $body = "Your request to join {$this->title} on Omeka Commons has been denied. ";
        $body .= "<a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a>";
        $email = $this->getEmailBase(array($user));
        $email->setSubject("Your request to join {$this->title} on Omeka Commons has been denied");
        $email->setBodyHtml($body);
        try {
            $email->send();
        } catch(Exception $e) {
            _log($e);
        }       
    }
    
    public function sendChangeStatusEmail($to, $newStatus)
    {
        switch ($newStatus) {
            case 'member':
                return;
                break;
                
            case 'admin':
                $newStatus = 'administrator';
                break;            
        }
        
        $body = "An administrator of {$this->title} on Omeka Commons has asked you to become an $newStatus.";
        $body .= "<a href='" . WEB_ROOT . "/groups/manage/" . $this->id . "'>{$this->title}</a>";
        $email = $this->getEmailBase(array($to));
        $email->setSubject("You have been asked to become an $newStatus in {$this->title}");
        $email->setBodyHtml($body);
        try {
            $email->send();
        } catch(Exception $e) {
            _log($e);
        }        
    }
    
    public function sendInvitationEmail($to, $message, $sender)
    {
        $mail = new Zend_Mail();
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->setFrom(get_option('administrator_email'), "Omeka Commons");
        foreach($to as $email) {
            $mail->addTo($email);
        }
        $subjectText = "An invitation to join the group '{$this->title}' in the Omeka Commons";
        $mail->setSubject($subjectText);        
        $body = "{$sender->name} has invited you to join the group {$this->title} in the Omeka Commons. Here's why:\n";
        $body .= $message;
        $body .= "<p>You can join the group <a href='" . WEB_ROOT . '/groups/my-groups' . "'>here</a></p>";
        $mail->setBodyHtml($body);
        try {
            $mail->send();
        } catch(Exception $e) {
            _log($e);
        }
        
    }
    
    private function getEmailBase($to = null)
    {
        $mail = new Zend_Mail();
        $from = get_option('administrator_email');
        $mail->setFrom($from, "Omeka Commons");
        if($to) {
           //$to could be either an array of email address or an array of users
           foreach($to as $data) {
               if(is_string($data)) {
                   $mail->addTo($data);
               } else {
                   $mail->addTo($data->email);
               }               
           } 
        } else {
            $owner = $this->getOwner();
            $mail->addTo($owner->email, $this->title . " Group Owner");            
        }        
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());      
        return $mail;         
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

    /**
     *
     * @param string $notification The name of an available notification. One of
     *      notify_member_joined
     *      notify_item_new
     *      notify_member_pending
     *      notify_member_left
     *      notify_item_deleted
     *
     */    
    
    public function findMembersForNotification($notification)
    {
        return get_db()->getTable('GroupMembership')->findUsersForNotification($this, $notification);
    }
    
    public function findAdmins()
    {
        return get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id, 'is_admin'=>1));
    }
    
    public function findOwner()
    {
        $owners =  get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id, 'is_owner'=>1));
        return $owners[0];        
    }
    
    public function findOwnerMembership()
    {
        $array = get_db()->getTable('GroupMembership')->findBy(array('group_id'=>$this->id, 'is_owner'=>1));
        return $array[0];        
    }
    
    public function findMembership($user)
    {
        if(is_numeric($user)) {
            $userId = $user;
        } else {
            $userId = $user->id;
        }
        $array = get_db()->getTable('GroupMembership')->findBy(array('group_id'=>$this->id, 'user_id'=>$userId));
        return $array[0];
    }
    
    public function getResourceId()
    {
        return 'Groups_Group';
    }
}