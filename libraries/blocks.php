<?php

class GroupsAddItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Add Item Block";
    const description = "Add items block";
    const plugin = "Groups";


    public function render()
    {
        $html = "<ul>";
        $groups = groups_groups_for_user();
        foreach($groups as $group) {
            $item = get_current_item();
            //check if item is already in the Group.
            if(!$group->hasItem($item)) {
                $html .= "<li id='groups-id-{$group->id}' class='groups-item-add'>{$group->title}</li>";
            }
        }
        $html .= "</ul>";
        return $html;
    }
}


class GroupsItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Item Block";
    const description = "Display the groups that are using an Item";
    const plugin = "Groups";


    public function render()
    {
        $html = "<h2>Groups</h2>";
        $groups = groups_groups_for_item();
        foreach($groups as $group) {
            $html .= "<div class='groups-group-block'>";
            $html .= "<h2><a href='". uri('groups/show/' . $group->id) ."' >{$group->title}</a></h2>";
            $html .= "<p>" . $group->description . "</p>";
            $html .= "</div>";
        }
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


