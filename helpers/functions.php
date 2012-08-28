<?php

/**
 * get the current group while looping through groups
 * @see /helpers/Function/loop_records()
 */

function groups_get_current_group()
{
    if (!($group = __v()->group)) {
        return false;
    }
    return $group;
}

/**
 * get the current group while looping through groups
 * @see /helpers/Function/loop_records()
 */


function groups_set_current_group($group)
{
    $view = __v();
    $view->previous_group = $view->group;
    $view->group = $group;
}

/**
 * get the tags on a group
 * this is not the tags on Items in a group, just the tags on a group!
 *
 * @return array Tag
 */

function groups_tags_for_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $tags = $group->Tags;
    return $tags;
}


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

function groups_tags_string_for_group($group = null, $uri = true)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $tags = groups_tags_for_group($group);
    if($uri) {
        $link = uri('groups/browse?tags=');
    }
    
    return tag_string($tags, $link);

}



/**
 * get the number of items associated with a group
 */

function groups_item_count($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getItemCount();
}

/**
 * get the number of members in a group
 *
 * @return int
 */

function groups_member_count($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getMemberCount();
}

/**
 * get an array of users in a group
 *
 * @return array Users in a group
 */

function groups_members_for_group($group = null, $sort = array())
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return $group->getMembers();
}


/**
 * get the items in a group
 *
 * @return array Item
 */

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

/**
 * get a link to the group for display
 * @return string
 */

function groups_link_to_group($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    $link = "<a href='".  record_uri($group, 'show') ."' >{$group->title}</a>";
    return $link;
}



function groups_group($field, $options = array(), $group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }

    //ignoring options for a while in anticipation of Omeka issue #187
    return html_escape($group->$field);
}

function groups_get_membership($group = null, $user = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    
    if(!$user) {
        $user = current_user();
    }
    
    if(!$user) {
        return false;
    }
    
    if(is_numeric($user)) {
        $userId = $user;
    } else {
        $userId = $user->id;
    }
    
    $table = get_db()->getTable('GroupMembership');
    $select = $table->getSelectForFindBy(array('user_id'=>$userId, 'group_id'=>$group->id));
    return $table->fetchObject($select);    
}

function groups_get_memberships($group = null, $all = false)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    if($all) {
        return get_db()->getTable('GroupMembership')->findBy(array('group_id'=>$group->id));
    }
    return get_db()->getTable('GroupMembership')->findBy(array('group_id'=>$group->id, 'is_pending'=>0));
    
}

function groups_group_visibility_text($group = null, $options=array())
{
    if(!$group) {
        $group = groups_get_current_group();
    }

    switch(groups_group('visibility', $options, $group)) {
        case 'open':
            return " -- Anyone may join and see all items";
        break;
        case 'public':
            return " -- Anyone can see items, but approval is required to join";
        break;
        case 'closed':
            return " -- Approval is required to join; items only visible to members";
        break;
    }

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


function groups_get_blocked_users($group = null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    return get_db()->getTable('GroupBlock')->findBy(array('blocker_id'=>$group->id, 'blocker_type'=>'Group'));
    
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


function groups_comments_for_group($group = null, $record=null)
{
    if(!$group) {
        $group = groups_get_current_group();
    }
    
    $params = array(
            'subject_record_type' => 'Group',
            'object_record_type' => 'Comment',
            'subject_id' => $group->id
    );
    //skip filter on membership in group
    if($record) {
        $objectParams = array('groups_skip_hook'=>true, 'record_id'=>$record->id, 'record_type'=>get_class($record));
    } else {
        $objectParams = array('groups_skip_hook'=>true);
    }
    
    return get_db()->getTable('RecordRelationsRelation')->findObjectRecordsByParams($params, array(), $objectParams);
}
