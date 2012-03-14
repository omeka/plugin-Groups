<?php


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
        return empty($this->groups);
    }

    public function render()
    {

        foreach($this->groups as $group) {
            $html .= "<div class='groups-group-block'>";
            $html .= "<h2>" . groups_link_to_group($group) . "</h2>";
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


