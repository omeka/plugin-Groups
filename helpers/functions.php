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


function groups_groups_for_item($item = null)
{
    if(!$item) {
        $item = get_current_item();
    }
    $db = get_db();
    $params = array(
        'hasItem'=>$item
    );
    $groups = $db->getTable('Group')->findBy($params);

    //need to filter out permissions to view items in the group
    //if you can't see the items in the group, you shouldn't see a link to the group from an item
    //can't see a way to do it via filters on the sql
    $currentUser = current_user();
    $acl = Omeka_Context::getInstance()->acl;
    $assertion = new GroupsAclAssertion;
    foreach($groups as $index=>$group) {

        if(! $assertion->assert($acl, $currentUser, $group, 'items')) {
            unset($groups[$index]);
        }
    }
    return $groups;


}

function groups_join_button($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }

    return "";


}