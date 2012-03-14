<?php


class GroupsMembersBlock extends Blocks_Block_Abstract
{
    const name = "Groups Members Block";
    const description = "List of group members";
    const plugin = "Groups";

    public function __construct($request = null, $blockConfig = null)
    {
        parent::__construct($request, $blockConfig);
        $this->group = groups_get_current_group();
        $this->members = groups_members_for_group($this->group);
    }

    public function isEmpty()
    {
        return empty($this->members);
    }

    public function render()
    {
        $group = groups_get_current_group();
        $html = "<ul class='groups-members'>";
        foreach($this->members as $user) {
            $html .= "<li>" . $user->name;
            if($user->id == $group->owner_id) {
                $html .= " *";
            }
            $html .= "</li>";
        }
        $html .= "</ul>";
        return $html;
    }
}

