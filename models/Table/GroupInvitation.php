<?php

class Table_GroupInvitation extends Omeka_Db_Table
{
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