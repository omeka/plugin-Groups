<?php

function groups_get_current_group()
{
    if (!($group = __v()->group)) {
        throw new Exception(__('A group has not been set to be displayed on this theme page! Please see Omeka documentation for details.'));
    }
    return $group;
}

function groups_set_current_group($group)
{
    $view = __v();
    $view->previous_group = $view->group;
    $view->group = $group;
}

function groups_tags_for_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $tags = $group->Tags;
    $html = "<ul class='tags'>";
    foreach($tags as $tag) {
        $html .= "<li>{$tag->name}</li>";
    }
    $html .= "</ul>";
    return $html;

}

function groups_item_count($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getItemCount();
}

function groups_member_count($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getMemberCount();
}

function groups_members_for_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getMembers();
}

function groups_items_for_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $db = get_db();
    $pred = $db->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(DCTERMS, 'references');
    $params = array(
        'object_record_type' => 'Item',
        'property_id' => $pred->id,
        'subject_id' => $group->id,
        'subject_record_type' => 'Group',
        'isPublic' => true
    );
    return $db->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params);

}


function groups_groups_for_user($user = null)
{
    if(!$user) {
        $user = current_user();
    }
    $db = get_db();
    return $db->getTable('Group')->findBy(array('user'=>$user));
}


function groups_groups_for_item($item)
{
    if(!$item) {
        $item = current_item();
    }
    $db = get_db();
    $pred = $db->getTable('RecordRelationsProperty')->findByVocabAndPropertyName(DCTERMS, 'references');
    $params = array(
        'object_id' => $item->id,
        'object_record_type' => 'Item',
        'property_id' => $pred->id,
        'subject_record_type' => 'Group',
        'isPublic' => true
    );
    return $db->getTable('RecordRelationsRelation')->findSubjectRecordsByParams($params);
}