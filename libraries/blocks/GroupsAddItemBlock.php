<?php


class GroupsAddItemBlock extends Blocks_Block_Abstract
{

    const name = "Groups Add Item Block";
    const description = "Add items block";
    const plugin = "Groups";


    public function __construct($request = null, $blockConfig = null)
    {
        parent::__construct($request, $blockConfig);
        $this->buildHtml();
    }

    public function isEmpty()
    {
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
