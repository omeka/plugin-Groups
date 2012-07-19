<?php

class GroupInvitationTable extends Omeka_Db_Table
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
    }   
    
    public function findInvitationToGroup($group_id, $user_id, $sender_id = null)
    {
        $params = array('group_id'=>$group_id, 'user_id'=>$user_id);
        if($sender_id) {
            $params['sender_id'] = $sender_id;
        }
        $select = $this->getSelectForFindBy($params);
        return $this->fetchObject($select);        
    }
}