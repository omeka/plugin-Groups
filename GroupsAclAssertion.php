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

    private $closedPrivileges = array();

    private $publicPrivileges = array(
                'items',
                );


    private $memberPrivileges = array(
                'addItem',
                'items',
                );

    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {
        //owner can do anything in the list of privileges passed
        if($resource->owner_id == $role->id) {
            if($privilege == 'join') {
                return false;
            }
            return true;
        }

        if(get_class($resource) == 'Group') {
            $isMember = $resource->hasMember($role);
            $arrayName = $isMember ? "memberPrivileges" : $resource->visibility . "Privileges";
            return in_array($privilege, $this->$arrayName);

        }
        return false;

    }
}