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
        return empty($this->groups);
    }

    public function render()
    {
        $html = "<ul class='groups-my-groups'>";
        foreach($this->groups as $group) {
            $html .= "<li>" . groups_link_to_group($group) . "</li>";
        }

        $html .= "</ul>";
        return $html;
    }
}
