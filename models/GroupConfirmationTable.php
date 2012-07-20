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
    }    
    
    public function findOrNew($params)
    {
        $select = $this->getSelect();
        $this->applySearchFilters($select, $params);
        $conf = $this->fetchObject($select);
        return $conf ? $conf : new GroupConfirmation();
    }
    
}