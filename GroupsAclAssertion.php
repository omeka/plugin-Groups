<?php


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
                );

    private $closedPrivileges = array('request');

    private $publicPrivileges = array(
                'items',
                'request'
                );


    private $memberPrivileges = array(
                'add-item',
                'items',
                'quit'
                );

    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {
        //if I'm passing in a groupId, dig that up to check permissions against that group
        //otherwise all that it checks against is the general 'Groups_Group' resource

        if(isset($_POST['groupId'])) {
            $resource = get_db()->getTable('Group')->find($_POST['groupId']);
        }

        //owner can do anything in the list of privileges passed
        if($resource->owner_id == $role->id) {
            switch($privilege) {
                case 'join':
                case 'quit':
                    return false;
                break;
                default:

                    return true;
                break;
            }
        }

        //sometimes we get a Group for the resource, sometimes just the Zend_Acl_Resource_Interface
        //AJAX requests like addItem pass up a groupId to check permissions on, so dig that up if it is set
        if($privilege == 'add-item' && isset($_POST['groupId'])) {
            $resource = get_db()->getTable('Group')->find($_POST['groupId']);
        }

        if(get_class($resource) == 'Group') {
            $isMember = $resource->hasMember($role);
            $arrayName = $isMember ? "memberPrivileges" : $resource->visibility . "Privileges";
            return in_array($privilege, $this->$arrayName);
        }

        return false;

    }
}