<?php
class GroupBlockTable extends Omeka_Db_Table
{

    public function getTableAlias() {
        if (empty($this->_name)) {
            $this->setTableName();
        }
    
        return $this->_name;
    }
    
    
    public function applySearchFilters($select, $params)
    {
        $columns = $this->getColumns();
        foreach($params as $param=>$value) {
            if(in_array($param, $columns)) {
                $select->where("$param = ?", $value );
            }
        }
    }
    
}