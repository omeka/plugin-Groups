<?php

class GroupsJoinBlock extends Blocks_Block_Abstract
{
    const name = "Groups Join Block";
    const description = "Links to join and manage membership";
    const plugin = "Groups";

    public function isEmpty()
    {
        return false;
    }

    public function render()
    {

        $group = groups_get_current_group();
        $currUser = current_user();
        if($currUser && !$group->hasMember($currUser)) {
            $html = "<p class='groups-join-button' id='groups-id-{$group->id}'>Join</p>";
            $html .= "<script type='text/javascript'>";
            $html .= "
                    jQuery(document).ready(
                            jQuery('p.groups-join-button').click(Omeka.Groups.join)
                        );
                    ";
            $html .= "</script>";
        }

        return $html;

    }



}



class GroupsAddItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Add Item Block";
    const description = "Add items block";
    const plugin = "Groups";


    public function isEmpty()
    {
        $this->buildHtml();
        return $this->isEmpty;
    }

    public function render()
    {
        return $this->html;
    }
    protected function buildHtml()
    {
        $this->isEmpty = true;
        //need to run through the items and build html to see if it is empty :(
        $this->html = "<ul>";
        $groups = groups_groups_for_user();
        foreach($groups as $group) {
            $item = get_current_item();
            //check if item is already in the Group.
            if(!$group->hasItem($item)) {
                $this->isEmpty = false;
                $this->html .= "<li id='groups-id-{$group->id}' class='groups-item-add'>{$group->title}</li>";
            }
        }
        $this->html .= "</ul>";
    }
}


class GroupsItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Item Block";
    const description = "Display the groups that are using an Item";
    const plugin = "Groups";

    public function __construct($request = null, $blockConfig = null)
    {
        parent::__construct($request, $blockConfig);
        $this->groups = groups_groups_for_item();
    }
    public function isEmpty()
    {
        if(count($this->groups) == 0) {
            return true;
        }
        return false;
    }

    public function render()
    {

        foreach($this->groups as $group) {
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


