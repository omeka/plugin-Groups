<?php

class GroupTable extends Omeka_Db_Table
{

    public function getTableAlias() {
        if (empty($this->_name)) {
            $this->setTableName();
        }
    
        return $this->_name;
    }    
    
    public function applySearchFilters($select, $params)
    {

        if(isset($params['groupsSearch']) && !empty($params['groupsSearch'])) {
            $this->filterBySearch($select, $params['groupsSearch']);
        }

        if (isset($params['tags'])) {
            $this->filterByTags($select, $params['tags']);
        }

        if(isset($params['visibility'])) {
            $this->filterByVisibility($select, $params['visibility']);
        }

        if(isset($params['user'])) {
            $this->filterByMembership($select, $params['user']);
        }

        if(isset($params['hasItem'])) {
            $this->filterByHasItem($select, $params['hasItem']);
        }

        if(isset($params['lacksItem'])) {
            $this->filterByHasItem($select, $params['hasItem'], true);
        }
    }

    public function __construct($targetModel, $db)
    {
        parent::__construct($targetModel, $db);
        $this->relationTable = $db->getTable('RecordRelationsRelation');
    }

    public function filterByTags($select, $tags)
    {
        // Split the tags into an array if they aren't already
        if (!is_array($tags)) {
            $tags = explode(get_option('tag_delimiter'), $tags);
        }

        $db = $this->getDb();

        //copied from ItemTable::filterByTags
        foreach ($tags as $tagName) {
            $subSelect = new Omeka_Db_Select;
            $subSelect->from(array('taggings'=>$db->Taggings), array('id'=>'taggings.relation_id'))
                ->joinInner(array('tag'=>$db->Tag), 'tag.id = taggings.tag_id', array())
                ->where('tag.name = ? AND taggings.`type` = "Group"', trim($tagName));
        }
        $select->where('groups.id IN (' . (string) $subSelect . ')');
    }

    public function filterByVisibility($select, $visibility)
    {
        $select->where("groups.visibility = ? ", $visibility);
    }

    public function filterByMembership($select, $user)
    {

        if(is_numeric($user)) {
            $userId = $user;
        } else {
            $userId = $user->id;
        }

        $db = $this->getDb();
        $membershipAlias = $db->getTable('GroupMembership')->getTableAlias();
        $alias = $this->getTableAlias();        
        $select->join(array($membershipAlias=>$db->GroupMembership), 
                        "$alias.id = $membershipAlias.group_id",
                        array()
                        );
        $select->where("$membershipAlias.user_id = $userId");        
    }

    public function filterByHasItem($select, $item, $negate = false)
    {
        if(is_numeric($item)) {
            $itemId = $item;
        } else {
            $itemId = $item->id;
        }
        $db = $this->getDb();
        $pred = $db->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(DCTERMS, 'references');
        if($negate) {
            $joinCondition = 'groups.id != record_relations_relations.subject_id';
        } else {
            $joinCondition = 'groups.id = record_relations_relations.subject_id';
        }

        $select->join(array('record_relations_relations'=>$db->RecordRelationsRelation), $joinCondition , array());
        $select->where("record_relations_relations.subject_record_type = 'Group'");
        $select->where("record_relations_relations.property_id = " . $pred->id);
        $select->where("record_relations_relations.object_record_type = 'Item'");
        $select->where("record_relations_relations.object_id = " . $itemId);

    }

    public function filterBySearch($select, $terms)
    {
        $db = get_db();
        $quotedTerms = $db->quote($terms);
        $select->where("MATCH (title, description) AGAINST ($quotedTerms)");
        //$tagsSelect = $this->getSelectForFindBy(array('tags'=>$terms));
        //copied from ItemTable::filterByTags
        

        $tags = explode(get_option('tag_delimiter'), $terms);
        foreach ($tags as $tagName) {
            $subSelect = new Omeka_Db_Select;
            $subSelect->from(array('taggings'=>$db->Taggings), array('id'=>'taggings.relation_id'))
                ->joinInner(array('tags'=>$db->Tag), 'tags.id = taggings.tag_id', array())
                ->where('tags.name = ? AND taggings.type = "Group"', trim($tagName));
        }
        $select->orWhere('groups.id IN (' . (string) $subSelect . ')');
        
    }  

}
