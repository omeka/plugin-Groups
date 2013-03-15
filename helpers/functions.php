<?php

/**
 * get the tags ON a group as a flat html list
 * this is not the tags on Items in a groups, just the tags on a group!
 *
 * @return string html <ul>
 */

function groups_tags_list_for_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $tags = groups_tags_for_group($group);
    $html = "<ul class='tags'>";
    foreach($tags as $tag) {
        $html .= "<li>{$tag->name}</li>";
    }
    $html .= "</ul>";
    return $html;
}

/**
 * get the tags ON a group as a string
 * this is not the tags on Items in a groups, just the tags on a group!
 *
 * @return string html <ul>
 */

function groups_tags_string_for_group($group, $uri = true)
{
    $tags = $group->Tags;
    if($uri) {
        $link = url('groups/browse?tags=');
    }
    return tag_string($tags, $link);
}

/**
 * get the groups that a user is part of
 *
 * @return array Group
 */

function groups_groups_for_user($user = null, $adminOnly = false)
{
    if(!$user) {
        $user = current_user();

    }
    if(!$user) {
        return array();
    }
    $db = get_db();
    $params = array('user_id'=>$user->id, 'is_pending'=>0);
    if($adminOnly) {
        $params['admin_or_owner'] = true;
    }
    return $db->getTable('GroupMembership')->findGroupsBy($params);
}

function groups_invitations_for_user($user = null)
{
    if(!$user) {
        $user = current_user();
    }
    
    if(!$user) {
        return array();
    }

    $db = get_db();
    return $db->getTable('GroupInvitation')->findBy(array('user_id'=>$user->id));
    
}

function groups_confirmations_for_user($user = null)
{
    if(!$user) {
        $user = current_user();
    }    
    if(!$user) {
        return array();
    }    
    $db = get_db();
    return $db->getTable('GroupConfirmation')->findBy(array('user_id'=>$user->id));        
}

function groups_membership_requested_admin($membership, $group)
{
    $db = get_db();
    $params = array('membership_id'=>$membership->id,
                    'group_id'=>$group->id,
                    'type'=>'make_admin');
    
    $confirmations = $db->getTable('GroupConfirmation')->findBy($params);
    return !empty($confirmations);
}


/**
 * get the groups that an Item is associated with
 *
 * @return array Item
 */

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

function groups_role_confirm($group = null, $membership=null, $role = 'admin')
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    
    if(!$membership) {
        $membership = groups_get_membership($group);
    }
    
    $confirmationTable = get_db()->getTable('GroupConfirmation');
    $select = $confirmationTable->getSelectForCount();
    $select->where("group_id = ?", $group->id);
    $select->where("membership_id = ?", $membership->id);
    $select->where("type =?", $role);
    $count =  $confirmationTable->count(array('group_id'=>$group->id,
                                            'membership_id'=>$membership->id,
                                            'type'=>$role            
            ));
    return $count;
}

/* Commenting-related functions */

/**
 * get the groups that a comment is associated with
 *
 * @return array Group
 */

function groups_groups_for_comment($comment)
{
    $params = array(
            'subject_record_type' => 'Group',
            'object_record_type' => 'Comment',
            'object_id' => $comment->id
    );
    return get_db()->getTable('RecordRelationsRelation')->findSubjectRecordsByParams($params);
}

