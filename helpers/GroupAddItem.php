<?php

class Group_View_Helper_GroupAddItem extends Zend_View_Helper_Abstract
{
    protected $_db;
    protected $_groups = array();
    protected $_item;
    protected $_user;

    public function groupAddItem($options = array())
    {
        $this->_setOptions($options);
        if(!$this->_user || empty($this->_groups) || !$this->_item) {
            return;
        }

        $user = $this->_user;
        $itemTitle = metadata($this->_item, array('Dublin Core', 'Title'));
        $userGroups = groups_groups_for_user($user);
        $itemUserGroups = array();

        foreach($userGroups as $userGroup) {
            if ($userGroup->hasItem($this->_item)) {
                $itemUserGroups[] = $userGroup;
            }
        }

        /* First show a plain text list of the user's groups that currently contain the item. */

        /* JS will use the empty class to determine which heading to show. */
        $emptyCheck = (count($itemUserGroups) < 1) ? 'empty' : '';
        $html  = "<div id='group-actions'>";
        $html .= "<div class='groups-item-status {$emptyCheck}'>";
        $html .= "<h3>" . __('%s is in your groups:', $itemTitle) . "</h3>";
        $html .= "<div class='item-user-groups'>";
        $html .= "<ul id='item-user-groups'>";
        foreach ($itemUserGroups as $itemUserGroup) {
            $html .= "<li id='item-user-group-{$itemUserGroup->id}'>" . link_to($itemUserGroup, 'show', $itemUserGroup->title) . "</li>";
        }
        $html .= "</ul>";
        $html .= "</div>";
        $html .= "<h3>" . __('%s is not in any of your groups.', $itemTitle) . "</h3>";
        $html .= "<button class='launch-add-item'>" . __('Select groups for item') . "</button>";
        $html .= "</div>";

        /* Markup used for Popeasy jQuery plugin to launch modal for selecting groups. */
        $html .= "<div class='overlay'></div>";
        $html .= "<div class='groups-add-item modal'>";
        $html .= "<div class='modal-header'>";
        $html .= "<a href='#' class='close-button'>" . __('Close') . "</a>";
        $html .= "<h3>" . __('Select groups for item') . "</h3>";
        $html .= "</div>";
        $html .= "<div class='modal-content'>";
        $html .= "<p>" . __('Once you add an item to your group, only a group administrator can remove the item. If you want to later remove this item from a group, make sure you have sufficient privileges.') . "</p>";
        $html .= "<ul id='user-groups'>";
        debug(count($this->_groups));
        foreach($this->_groups as $group) {
            $groupId = $group->id;
            $adminState = ($user->id == $group->owner_id) ? 'admin' : '';
            if($group->hasItem($this->_item)) {
                if($user->id == $group->owner_id) {
                    $html .= "<li id='groups-id-{$groupId}' class='groups-item-exists checked {$adminState}'>";
                    $html .= "<a href='#'>{$group->title}</a>" .  "<input type='checkbox' val='1' name='select-group-{$groupId}' checked>";
                } else {
                    $html .= "<li id='groups-id-{$groupId}' class='groups-item-ineditable {$adminState}'>";
                    $html .= $group->title;
                }
                $html .= "</li>";
            } else {
                $html .= "<li id='groups-id-{$groupId}' class='groups-item-add {$adminState}'><a href='#'>{$group->title}</a> <input type='checkbox' val='0' name='select-group-{$groupId}'></li>";
            }
        }
        $html .= "</ul>";
        $html .= "<button class='add-to-groups close-button'>" . __('Select Groups') . "</button>";
        $html .= "</div></div></div>";
        return $html;
    }

    protected function _setOptions($options)
    {
        $this->_db = get_db();
        $this->_setUser($options);
        $this->_setItem($options);
        $this->_setGroups($options);
    }

    protected function _setUser($options)
    {
        if(isset($options['user'])) {
            $this->_user = $options['user'];
        } else {
            $this->_user = current_user() ? current_user() : false;
        }
    }

    protected function _setGroups($options)
    {
        if(!$this->_user) {
            $this->_groups = array();
        }
        $options['is_pending'] = false;
        $this->_groups =  $this->_db->getTable('GroupMembership')->findGroupsBy($options);
    }

    protected function _setItem($options)
    {
        if(isset($options['item'])) {
            $this->_item = $options['item'];
        } else if($item = get_view()->item) {
            $this->_item = $item;
        } else {
            $this->_item = false; // this should probably throw an exception
        }
    }
}