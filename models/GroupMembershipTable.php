<?php

class GroupMembershipTable extends Omeka_Db_Table
{
    
    public function applySearchFilters($select, $params)
    {
        $columns = $this->getColumns();
        $alias = $this->getTableAlias();
        foreach($params as $param=>$value) {

            if($param == 'admin_or_owner') {
                $select->where("$alias.is_owner = 1 OR $alias.is_admin = 1");
            }
            if(in_array($param, $columns)) {
                $select->where("$alias.$param = ?", $value );
            }      
        }
    }
    
    /**
     * 
     * 
     * @param mixed $group Group or group id
     * @param string $notification The name of an available notification. One of 
     *      notify_member_joined
     *      notify_item_new
     *      notify_member_pending
     *      notify_member_left
     *      notify_item_deleted
     *  
     */

    public function findUsersForNotification($group, $notification)
    {
        if(is_numeric($group)) {
            $groupId = $group;
        } else {
            $groupId = $group->id;
        }
        $alias = $this->getTableAlias();
        $db = $this->getDb();        
        $userTable = $this->getTable('User');
        $userTableAlias = $userTable->getTableAlias();
        $select = $userTable->getSelect();
        $select->join(array($alias=>$db->GroupMembership), "$userTableAlias.id = $alias.user_id", array());
        $select->where("$alias.group_id = $groupId");
        $select->where("$alias.$notification = 1");        
        return $userTable->fetchObjects($select);        
    }

    /**
     * Find the Users corresponding to the GroupMemberships
     * @param array $params
     */
    
    public function findUsersBy($params = array(), $sort = array())
    {
        $db = $this->getDb();
        $userTable = $this->getTable('User');
        $userTableAlias = $userTable->getTableAlias();
        $select = $userTable->getSelect();
        $alias = $this->getTableAlias();
        $select->join(array($alias=>$db->GroupMembership), "$userTableAlias.id = $alias.user_id", array());
        $this->applySearchFilters($select, $params);
        $userTable->applySorting($select, $sort['sort_field'], $sort['sort_dir']);
        return $userTable->fetchObjects($select);
    }
    
    public function findGroupsBy($params = array())
    {
        $db = $this->getDb();
        $groupTable = $this->getTable('Group');
        $groupTableAlias = $groupTable->getTableAlias();
        $select = $groupTable->getSelect();
        $alias = $this->getTableAlias();
        $select->join(array($alias=>$db->GroupMembership), "$groupTableAlias.id = $alias.group_id", array());
        $this->applySearchFilters($select, $params);
        return $groupTable->fetchObjects($select);        
    }    
}