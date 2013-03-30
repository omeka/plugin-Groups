<?php

class Group_View_Helper_UserGroups extends Zend_View_Helper_Abstract
{
    
    public function userGroups($user = null)
    {
        if(!$user) {
            $user = current_user();
        }

        if(!$user) {
            return '';
        }
        
        $groups = groups_groups_for_user($user);
        $label = __("Groups for ") . $user->name;
        
        $html = "<div class='groups-user-groups'>";
        
        if(empty($groups)) {
            $html .= "<p>" . __('You do not belong to any groups.') . ' ' . "<a href='" . url('groups/add') . "'>" . __('Create a group') . "</a></p>";
        } else {
            $html .= "<h3><a href='" . url('groups/my-groups') . "'>$label</a></h3>";
            $html .= "<ul class='groups-my-groups'>";
            foreach($groups as $group) {
                $html .= "<li>" . link_to($group, 'show', metadata($group, 'title')) . "</li>";
            }
            $html .= "</ul>";
        }
        $html .= "</div>";
        return $html;
    }
}