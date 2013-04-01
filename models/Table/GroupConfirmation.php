<?php

class Table_GroupConfirmation extends Omeka_Db_Table
{
    
    public function applySearchFilters($select, $params)
    {
        if(array_key_exists('user_id', $params)) {            
            $db = $this->getDb();
            $membershipTable = $db->getTable('GroupMembership');
            $mtAlias = $membershipTable->getTableAlias();
            $select->join(array($mtAlias=>$db->GroupMembership),
                           "$mtAlias.id = group_confirmations.membership_id", array()
                            );
            $select->where( "$mtAlias.user_id = {$params['user_id']}");
        }
        parent::applySearchFilters($select, $params);
    }    
    
    public function findOrNew($params)
    {
        $select = $this->getSelect();
        $this->applySearchFilters($select, $params);
        $conf = $this->fetchObject($select);
        return $conf ? $conf : new GroupConfirmation();
    }
    
}