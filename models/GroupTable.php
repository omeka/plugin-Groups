<?php

class GroupTable extends Omeka_Db_Table
{
    protected $_alias = 'g';

    public function applySearchFilters($select, $params)
    {
           // filter based on tags
        if (isset($params['tags'])) {
            $this->filterByTags($select, $params['tags']);
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

    public function findUsersForGroup($group)
    {
        $predId = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');
        $params = array(
            'object_record_type' => 'User',
            'property_id' => $predId,
            'subject_record_type' => 'Group',
            'subject_id' => $group->id,
            'isPublic' => true
        );
        return $this->relationTable->findObjectRecordsByParams($params);

    }

    public function findItemsForGroup($group)
    {
        $predId = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(DCTERMS, 'references');
        $params = array(
            'object_record_type' => 'Item',
            'property_id' => $predId,
            'subject_id' => $group->id,
            'subject_record_type' => 'Group',
            'isPublic' => true
        );
        return $this->relationTable->findObjectRecordsByParams($params);

    }



    public function findGroupsForUser($user)
    {
        $predId = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(SIOC, 'has_member');
        $params = array(
            'object_id' => $user->id,
            'object_record_type' => 'User',
            'property_id' => $predId,
            'subject_record_type' => 'Group',
            'isPublic' => true
        );
        return $this->relationTable->findSubjectRecordsByParams($params);
    }

    public function findGroupsForItem($item)
    {
        $predId = get_db()->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(DCTERMS, 'references');
        $params = array(
            'object_id' => $item->id,
            'object_record_type' => 'Item',
            'property_id' => $predId,
            'subject_record_type' => 'Group',
            'isPublic' => true
        );
        return $this->relationTable->findSubjectRecordsByParams($params);
    }
}
