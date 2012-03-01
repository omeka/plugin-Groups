<?php


class GroupsItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Item Block";
    const description = "Display the groups that are using an Item";
    const plugin = "Groups";


    public function render()
    {
        $html = "<div class='block'>";
        $html .= "<h2>Groups</h2>";
        $groups = groups_groups_for_item();
        foreach($groups as $group) {
            $html .= "<div class='groups-group-block'>";
            $html .= "<h2><a href='". uri('groups/group/show/id/' . $group->id) ."' >{$group->title}</a></h2>";
            $html .= "<p>" . $group->description . "</p>";
            $html .= "</div>";
        }

        $html .= "</div>";
        return $html;
    }

    static function prepareConfigOptions($formData)
    {
        return false;
    }

    static function formElementConfigData()
    {
        return false;
    }

}


