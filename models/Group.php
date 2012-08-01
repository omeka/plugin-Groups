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
        $count = $this->getDb()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'user_id'=>$user->id));
        if($count == 0) {
            $membership = new GroupMembership;
            $membership->unsetOptions();
            $membership->user_id = $user->id;
            $membership->group_id = $this->id;
            $membership->is_pending = $pending;
            switch($role) {
                case 'is_admin':
                    $membership->is_admin = 1;
                    $membership->is_owner = 0;
                    break;
                    
                case 'is_owner':
                    $membership->is_admin = 1;
                    $membership->is_owner = 1;
                    break;
                    
                default:
                    $membership->is_admin = 0;
                    $membership->is_owner = 0;
                break;   
            }
            $membership->save();
            return $membership;
        }
        return false;
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
        $subject = "A new member wants to join {$this->title} on " . settings('site_title');
        $body = "User {$user->name} has requested membership <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on Omeka Commons. You can log into Omeka Commons and manage memberships here: ";
        $this->sendEmails($to, $subject, $body);        
    }
    
    public function sendNewMemberEmail($user, $to=null)
    {
        $subject = "A new member has joined {$this->title} on " . settings('site_title');
        $body = "A new member {$user->name} has joined the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on " . settings('site_title');
        $this->sendEmails($to, $body, $subject);     
    }

    public function sendMemberLeftEmail($user, $to=null)
    {
        $subject = "A member has left {$this->title} on " . settings('site_title');
        $body = "{$user->name} has left the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on" . settings('site_title');
        $this->sendEmails($to, $body, $subject);                
    }

    public function sendNewItemEmail($item, $to = null, $user)
    {
        $subject = "A new item has been added to {$this->title} on " . settings('site_title');
        $body = "{$user->name} ({$user->username}) has added an item to the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on " . settings('site_title');
        $body .= "<a href='" . abs_item_uri($item) . "'>" . item('Dublin Core', 'Title', array(), $item) . "</a>";
        $this->sendEmails($to, $body, $subject);
    }

    public function sendMemberApprovedEmail($user)
    {
        $body = "Your request to join {$this->title} on Omeka Commons has been approved. ";
        $body .= "<a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a>";
        $subject = "Your request to join {$this->title} on Omeka Commons has been approved!";
        $this->sendEmails($user, $body, $subject);        
    }

    public function sendMemberDeniedEmail($user) 
    {        
        $body = "Your request to join {$this->title} on Omeka Commons has been denied. ";
        $body .= "<a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a>";
        $subject = "Your request to join {$this->title} on Omeka Commons has been denied";
        $this->sendEmails($user, $body, $subject);       
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
        $subject = "You have been asked to become an $newStatus in {$this->title}";
        $this->sendEmails($to, $body, $subject);
    }
    
    public function sendInvitationEmail($to, $message, $sender)
    {
        $subject = "An invitation to join the group '{$this->title}' on " . settings('site_title');
        $body = "<p>{$sender->name} has invited you to join the group {$this->title} on " . settings('site_title');
        $body .= "<p>Here's their message</p>";
        $body .= "<p>$message</p>";
        $body .= "<p>You can join the group <a href='" . WEB_ROOT . '/groups/my-groups' . "'>here</a></p>";
        foreach($to as $email) {
            $this->sendEmails($email, $body, $subject);
        }
    }
    
    public function sendInvitationDeclinedEmail($user, $to)
    {
        $body = "<p>{$user->name} has declined your invitation to join {$this->title} ";
        $subject = "You're invitation to {$user->name} to join {$this->title} was declined";
        $this->sendEmails($email, $body, $subject );
    }
    
    private function sendEmail($to, $body, $subject)
    {
        if(is_string($to)) {
            $email = $to;
        }
        if($to instanceOf User) {
            $email = $to->email;
        }        
        if($to instanceOf GroupMember) {
            $email = $to->User->email;
        }
        $mail = new Zend_Mail();
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->setFrom(get_option('administrator_email'), settings('site_title'));
        $mail->addTo($email);
        $mail->setSubject($subject);
        $mail->setBodyHtml($body);        
        try {
            $mail->send();
        } catch(Exception $e) {
            _log($e);
        }
    }
    
    private function sendEmails($to, $body, $subject) 
    {
        if(is_array($to)) {
            foreach($to as $user) {
                $this->sendEmail($to, $body, $subject);
            }
        } else {
            $this->sendEmail($to, $body, $subject);
        }
    
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