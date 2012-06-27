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
}