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
    $tags = $group->Tags;
    $html = "<ul class='tags'>";
    foreach($tags as $tag) {
        $tagName = $tag->name;
        $html .= "<li><a href='".url('groups/browse?tags='.$tagName)."'>".$tagName."</a></li>";
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
        $link = 'groups/browse';
    } else {
        $link = null;
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

function groups_invitation_to_group($group, $user = null)
{
    if(!$user) {
        $user = current_user();
    }
    
    return get_db()->getTable('GroupInvitation')->findInvitationToGroup($group->id, $user->id);
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
        $item = get_current_record('item');
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
    $acl = Zend_Registry::get('bootstrap')->getResource('Acl');
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
        $membership = $group->getMembership(array('user_id'=>current_user()->id));
    }
    if(empty($membership) || !$membership->exists()) {
        return false;
    }

    $confirmationTable = get_db()->getTable('GroupConfirmation');
    $count = $confirmationTable->count(array('group_id'      => $group->id,
                                             'membership_id' => $membership->id,
                                             'type'          => $role
    ));

    return $count;
}

function get_random_featured_groups($num)
{
    return get_records('Group', array('featured' => 1, 'sort_field' => 'random'), $num);

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

