<?php

class Group extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $id;
    public $title;
    public $description;
    public $visibility;    
    public $owner_id;

    protected $_related = array('Tags' => 'getTags', 'Items'=>'getItems');

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Tag($this);
        $this->_mixins[] = new Mixin_Owner($this);
        $this->_mixins[] = new Mixin_Search($this);
    }

    public function addMember($user, $pending = 0, $role = null)
    {
        $count = $this->getDb()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'user_id'=>$user->id));
        if($count == 0) {
            $membership = new GroupMembership();
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
            $membership = $this->getMembership(array('user_id'=>$user->id));
        } elseif($user instanceof GroupMembership) {
            $membership = $user;
        }        
        $membership->delete();
    }

    public function approveMember($user)
    {
        if($user instanceof User) {
            $membership = $this->getMembership(array('user_id'=>$user->id));
        } elseif($user instanceof GroupMembership) {
            $membership = $user;
        }
        
        $membership->is_pending = false;
        $membership->save();
    }

    public function denyMembership($user)
    {
        if($user instanceof User) {
            $membership = $this->getMembership(array('user_id'=>$user->id));
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
        $params = $this->buildParams($item, DCTERMS, 'references');
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);        
        $rel->delete();
    }

    public function setRelationPublic($record, $prefix, $localpart, $public)
    {
        $params = $this->buildParams($record, $prefix, $localpart);
        $rel = get_db()->getTable('RecordRelationsRelation')->findOne($params);
        $rel->public = $public;
        $rel->save();
    }

    public function getItems()
    {
        $params = $this->buildParams('Item', DCTERMS, 'references');
        return $this->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params);
    }

    public function hasItem($item)
    {
        if(is_numeric($item)) {
            $itemId = $item;
        } else {
            $itemId = $item->id;
        }
        $params = $this->buildParams('Item', DCTERMS, 'references');
        $params['object_id'] = $itemId;
        return (bool) get_db()->getTable('RecordRelationsRelation')->count($params);
    }

    public function getMembers($params = array(), $sort = array())
    {
        if(!isset($params['group_id'])) {
            $params['group_id'] = $this->id;
        }
        return get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id), $sort);
    }    

    public function getProperty($property)
    {
        switch($property) {
            case 'members_count':
                return $this->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'is_pending'=>false));
                break;
            
            case 'items_count':
                $params = $this->buildParams('Item', DCTERMS, 'references');
                return get_db()->getTable('RecordRelationsRelation')->count($params);                
                break;
                
            case 'visibility':
                return ucfirst($this->visibility);
                break;
            default: 
                return parent::getProperty($property);
        }
    }
    
    public function hasMember($user)
    {
        if(!$user) {
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
        if(!$user) {
            return false;
        }
        $count = get_db()->getTable('GroupMembership')->count(array('group_id'=>$this->id, 'is_pending'=>true, 'user_id'=>$user->id));
        if($count == 0) {
            return false;
        }
        return true;
    }

    public function getMemberRequests()
    {
        return get_db()->getTable('GroupMembership')->findUsersBy(array('group_id'=>$this->id, 'is_pending'=>true));
    }
    
    public function removeComment($commentId)
    {
        $ownsComment = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName('http://ns.omeka-commons.org/', 'ownsComment');
        $params = array(
                    'subject_record_type' => 'Group',
                    'subject_id' => $this->id,
                    'object_record_type' => 'Comment',
                    'object_id' => $commentId,
                    'property_id' => $ownsComment->id
                );        
        $rels = get_db()->getTable('RecordRelationsRelation')->findBy($params);
        $rel = $rels[0];
        $rel->delete();
    }

    private function newRelation($object, $prefix, $localpart, $public = true)
    {
        $props = $this->buildParams($object, $prefix, $localpart);
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
        $subject = "A new member wants to join {$this->title} on " . get_option('site_title');
        $body = "User {$user->name} has requested membership <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on Omeka Commons. You can log into Omeka Commons and manage memberships here: ";
        $this->sendEmails($to, $subject, $body);        
    }
    
    public function sendNewMemberEmail($user, $to=null)
    {
        $subject = "A new member has joined {$this->title} on " . get_option('site_title');
        $body = "A new member {$user->name} has joined the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on " . get_option('site_title');
        $this->sendEmails($to, $body, $subject);     
    }

    public function sendMemberLeftEmail($user, $to=null)
    {
        $subject = "A member has left {$this->title} on " . get_option('site_title');
        $body = "{$user->name} has left the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on" . get_option('site_title');
        $this->sendEmails($to, $body, $subject);                
    }

    public function sendNewItemEmail($item, $to = null, $user)
    {
        $subject = "A new item has been added to {$this->title} on " . get_option('site_title');
        $body = "{$user->name} ({$user->username}) has added an item to the <a href='" . WEB_ROOT . "/groups/show/" . $this->id . "'>{$this->title}</a> group on " . get_option('site_title');
        $body .= "<a href='" . absolute_url(link_to($item, 'show', metadata($item, array('Dublin Core', 'Title')))) . "'></a>";
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
        $subject = "An invitation to join the group '{$this->title}' on " . get_option('site_title');
        $body = "<p>{$sender->name} has invited you to join the group {$this->title} on " . get_option('site_title');
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
            _log('is string');
        }
        if($to instanceOf User) {
            $email = $to->email;
            _log('is user');
        }        
        if($to instanceOf GroupMember) {
            $email = $to->User->email;
            _log('is groupmember');
        }
        _log($email);
        
        $mail = new Zend_Mail();
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        $mail->setFrom(get_option('administrator_email'), get_option('site_title'));
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
                $this->sendEmail($user, $body, $subject);
            }
        } else {
            $this->sendEmail($to, $body, $subject);
        }
    }

    protected function afterSave($args)
    {
        $post = $args['post'];
        $this->applyTagString($post['tags'], ',');
        $this->setSearchTextTitle($this->title);
        $this->addSearchText($this->description);
    }

    private function buildParams($record, $prefix, $localpart){

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
    
    public function getMembersForNotification($notification)
    {
        return get_db()->getTable('GroupMembership')->findUsersForNotification($this, $notification);
    }
    
    public function getMembership($params=array())
    {
        if(!isset($params['group_id'])) {
            $params['group_id'] = $this->id;
        }
        
        //if the current user has high enough role on the site (Super or Admin),
        //give an owner membership
        $currentUser = current_user();
        $currentUserRole = metadata($currentUser, 'role');
        if($currentUserRole == 'admin' || $currentUserRole == 'super') {
            $ownerMembership = new GroupMembership;
            $ownerMembership->group_id = $this->id;
            $ownerMembership->user_id = $currentUser->id;
            $ownerMembership->is_owner = 1;
            $ownerMembership->is_pending = 0;
            $ownerMembership->id = 0;
            return $ownerMembership;
        }
        
        $array = $this->getTable('GroupMembership')->findBy($params, 1);
        return isset($array[0]) ? $array[0] : false;
    }
    
    public function getMemberships($params = array())
    {
        if(!isset($params['group_id'])) {
            $params['group_id'] = $this->id;
        }
        
        return $this->getTable('GroupMembership')->findBy($params);
    }
    
    public function getBlockedUsers()
    {
        return $this->getTable('GroupBlock')->findBy(array('blocker_id'=>$this->id, 'blocker_type'=>'Group'));
    }
    
    public function getComments($record = null) 
    {
        
        $params = array(
                'subject_record_type' => 'Group',
                'object_record_type' => 'Comment',
                'subject_id' => $group->id
        );
        //skip filter on membership in group
        if($record) {
            $objectParams = array('groups_skip_hook'=>true, 'record_id'=>$record->id, 'record_type'=>get_class($record));
        } else {
            $objectParams = array('groups_skip_hook'=>true);
        }
        
        return get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params, array(), $objectParams);                
    }    
    
    public function getResourceId()
    {
        return 'Groups_Group';
    }
    
    public function visibilityText()
    {
        switch(metadata($this, 'visibility')) {
            case 'Open':
                return "Anyone may join and see all items";
                break;
            case 'Public':
                return "Approval is required to join; items visible to everyone";
                break;
            case 'Closed':
                return "Approval is required to join; items only visible to members";
                break;
        }        
    }
}