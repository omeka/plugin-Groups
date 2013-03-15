<?php

require_once(GROUPS_PLUGIN_DIR . '/helpers/functions.php');

class GroupsAclAssertion implements Zend_Acl_Assert_Interface
{

    /**
     *
     * editSelf assertion is handles by Omeka_Acl_Assert_Ownership
     * everyone can add, so don't need to handle it here
     * same with show, at least the basic group (not including item-visibility permissions)
     */

    private $openPrivileges = array(
                'items',
                'join',
                'invitations'
                );

    private $closedPrivileges = array('request');

    private $publicPrivileges = array(
                'items',
                'request'            
                );

    private $memberPrivileges = array(
                'add-item',
                'items',
                'quit',
                'manage'
                );

    
    private $adminPrivileges = array(
            'add-item',
            'items',
            'approve-request',
            'remove-member',
            'make-admin',
            'manage',
            'invitations',
            'administration',
            'change-status',
            'block',
            'unblock',
            'remove-comment',
            'quit'
            );

    private $ownerPrivileges = array(
            'add-item',
            'items',
            'edit',
            'invitations',
            'approve-request',
            'make-admin',
            'manage',
            'make-owner',
            'administration',
            'change-status',
            'remove-member',
            'block',
            'unblock',
            'remove-comment',
    );    
    
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {    
        $db = get_db();
        //if I'm passing in a groupId, dig that up to check permissions against that group
        //otherwise all that it checks against is the general 'Groups_Group' resource

        //sometimes we get a Group for the resource, sometimes just the Zend_Acl_Resource_Interface
        //AJAX requests like addItem pass up a groupId to check permissions on, so dig that up if it is set
        
        if(isset($_POST['groupId'])) {
            $resource = $db->getTable('Group')->find($_POST['groupId']);
        }
        
        //always get to my-groups
        if($privilege == 'my-groups') {
            return true;
        }                                

        if(get_class($resource) == 'Group') {
            $membership = $resource->getMembership(array('user'=>$role));
           // $membership = groups_get_membership($resource, $role);
            $blockTable = $db->getTable('GroupBlock');
            if($role->id) {
                $blockParams = array(
                        'blocked_id'=>$role->id,
                        'blocked_type'=>'User',
                        'blocker_id'=>$resource->id,
                        'blocker_type'=>'Group'
                );
                $block = $blockTable->count($blockParams);
            }

            switch($privilege) {
                
                case 'join':
                    if($block) {
                        return false;
                    }

                    //to test for join permission, first see if current user has been invited by an owner or admin
                    $invitation = $db->getTable('GroupInvitation')->findInvitationToGroup($resource->id, $role->id);
                    if($invitation) {
                        $senderMembership = groups_get_membership($resource, $invitation->sender_id);
                        if($senderMembership->is_owner || $senderMembership->is_admin) {
                            return true;
                        }
                    }                
                    break;
                    
                case 'request' :
                    if($block) {
                        return false;
                    }
                    break;
                
                case 'invitations':
                    //can send an invitation if group is open, or user is owner or admin
                    if($resource->visibility == 'open') {
                        return true;                    
                    } else {
                         if($membership) {
                             return $membership->is_admin || $membership->is_owner;
                         }                     
                    }
                    break;
                
                case 'manage':
                    //if user has a membership, they can manage it
                    return $membership;
                    break;
                
                case 'quit':
                    //don't let owners quit on their flock
                    if($membership->owner_id == 1) {
                        return false;
                    }
                    break;
                            
            }
            //$membership = groups_get_membership($resource, $role);
                 
            if($membership) {                      
                if($membership->is_admin) {
                    return in_array($privilege, $this->adminPrivileges);
                }
                if($membership->is_owner) {    
                    return in_array($privilege, $this->ownerPrivileges);                    
                }
                return in_array($privilege, $this->memberPrivileges);
            }
            $arrayName = $resource->visibility . "Privileges";
            return in_array($privilege, $this->$arrayName);
            
         
        }
        //check to see if there is a potential reason to give access. the controller will sort out details with has_permission()
        //rough pass, if user and is a member of any group
        
        $groups = groups_groups_for_user($role);
        
        if(count($groups) != 0) {
            return true;
        }
        return false;
    }
}