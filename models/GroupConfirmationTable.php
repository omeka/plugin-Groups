<?php

class GroupConfirmationTable extends Omeka_Db_Table
{
    
    public function applySearchFilters($select, $params)
    {
        $columns = $this->getColumns();
        $alias = $this->getTableAlias();
        foreach($params as $param=>$value) {
            if(in_array($param, $columns)) {
                $select->where("$alias.$param = ?", $value );
            }
        }
        if(array_key_exists('user_id', $params)) {            
            $membershipTable = $this->getDb()->getTable('GroupMembership');
            $mtAlias = 'omeka_group_memberships';
            $select->join(array($mtAlias, $membershipTable),
                           "$mtAlias.id = group_confirmations.membership_id", array()
                            );
            $select->where( "$mtAlias.user_id = {$params['user_id']}");
        }
    }    
    
    public function findOrNew($params)
    {
        $select = $this->getSelect();
        $this->applySearchFilters($select, $params);
        $conf = $this->fetchObject($select);
        return $conf ? $conf : new GroupConfirmation();
    }
    
}