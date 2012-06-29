<?php

class GroupsMyGroupsBlock extends Blocks_Block_Abstract
{
    const name = "Groups My Groups Block";
    const description = "List of current user's groups";
    const plugin = "Groups";

    public function __construct($request = null, $blockConfig = null)
    {
        parent::__construct($request, $blockConfig);
        $this->groups = groups_groups_for_user();
    }

    public function isEmpty()
    {
        return ! current_user();
    }

    public function render()
    {
        $html = "<p><a href='" . uri('groups/my-groups') . "'>Manage Groups</a></p>";
        $invitations = groups_invitations_for_user();
        if(!empty($invitations)) {
            $html .= "<p>You have <a href='" . public_uri('groups/my-groups') . "'>pending invitations</a></p>";
        }
        if(empty($this->groups)) {
            $html .= "<p>You do not belong to any groups. Why not <a href='" . public_uri('groups/add') . "'>Create one</a>?</p>";
        } else {
            $html .= "<ul class='groups-my-groups'>";
            foreach($this->groups as $group) {
                $html .= "<li>" . groups_link_to_group($group) . "</li>";
            }            
            $html .= "</ul>";            
        }

        return $html;
    }
}
