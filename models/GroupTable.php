<?php

class GroupTable extends Omeka_Db_Table
{
    protected $_alias = 'g';

    public function getSelect()
    {
        $select = new Omeka_Db_Select($this->getDb()->getAdapter());
        $alias = $this->getTableAlias();
        $select->from(array($alias=>$this->getTableName()), "$alias.*");
        $acl = Omeka_Context::getInstance()->acl;
        if ($acl) {
            new GroupPermissions($select, $acl);
        }

        return $select;
    }

    public function applySearchFilters($select, $params)
    {
           // filter based on tags
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
            $subSelect->from(array('tg'=>$db->Taggings), array('id'=>'tg.relation_id'))
                ->joinInner(array('t'=>$db->Tag), 't.id = tg.tag_id', array())
                ->where('t.name = ? AND tg.`type` = "Group"', trim($tagName));

            $select->where('g.id IN (' . (string) $subSelect . ')');
        }
    }

    public function filterByVisibility($select, $visibility)
    {
        $select->where("g.visibility = ? ", $visibility);
    }

    public function filterByMembership($select, $user)
    {

        if(is_numeric($user)) {
            $userId = $user;
        } else {
            $userId = $user->id;
        }

        $db = $this->getDb();
        $prop = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');

        $select->join(array('rr'=>$db->RecordRelationsRelation), 'g.id = rr.subject_id', array());
        $select->where("rr.subject_record_type = 'Group'");
        $select->where("rr.property_id = " . $prop->id);
        $select->where("rr.object_record_type = 'User'");
        $select->where("rr.object_id = " . $userId);
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
            $joinCondition = 'g.id != rr.subject_id';
        } else {
            $joinCondition = 'g.id = rr.subject_id';
        }

        $select->join(array('rr'=>$db->RecordRelationsRelation), $joinCondition , array());
        $select->where("rr.subject_record_type = 'Group'");
        $select->where("rr.property_id = " . $pred->id);
        $select->where("rr.object_record_type = 'Item'");
        $select->where("rr.object_id = " . $itemId);

    }

}
