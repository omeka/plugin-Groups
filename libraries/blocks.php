<?php

class GroupsMembersBlock extends Blocks_Block_Abstract
{
    const name = "Groups Members Block";
    const description = "List of group members";
    const plugin = "Groups";

    public function isEmpty()
    {
        $group = groups_get_current_group();
        $this->members = groups_members_for_group($group);
        return empty($this->members);
    }

    public function render()
    {
        $html = "<ul class='groups-members'>";
        foreach($this->members as $user) {
            $html .= "<li>" . $user->name . "</li>";
        }
        $html .= "</ul>";
        return $html;
    }
}

class GroupsJoinBlock extends Blocks_Block_Abstract
{
    const name = "Groups Manage Block";
    const description = "Links to manage membership";
    const plugin = "Groups";

    public function isEmpty()
    {
        return ! current_user();
    }

    public function render()
    {

        $group = groups_get_current_group();
        $currUser = current_user();
        $isOwner = $group->isOwnedBy($currUser);
        if($group->hasMember($currUser) ) {
            if($isOwner) {
                $html = "<p>Pending Membership Requests</p>";
                $html .= "<ul class='groups-pending-requests'>";
                $html .= $this->listPendingRequests($group);
                $html .= "</ul>";
            } else {
                $html = "<p class='groups-quit-button groups-button' id='groups-id-{$group->id}'>Leave</p>";
                $html .= "<script type='text/javascript'>";
                $html .= "
                        jQuery(document).ready(
                                jQuery('p.groups-quit-button').click(Omeka.Groups.quit)
                            );
                        ";
                $html .= "</script>";
            }

        } else {
            if($group->visibility == 'open') {
                $html = "<p class='groups-join-button groups-button' id='groups-id-{$group->id}'>Join</p>";
                $html .= "<script type='text/javascript'>";
                $html .= "
                        jQuery(document).ready(
                                jQuery('p.groups-join-button').click(Omeka.Groups.join)
                            );
                        ";
                $html .= "</script>";
            } else {
                if($group->hasPendingMember($currUser)) {
                    $html = "<p class='groups-pending'>Membership request is pending</p>";

                } else {
                    $html = "<p class='groups-request-button groups-button' id='groups-id-{$group->id}'>Request Membership</p>";
                    $html .= "<script type='text/javascript'>";
                    $html .= "
                            jQuery(document).ready(
                                    jQuery('p.groups-request-button').click(Omeka.Groups.request)
                                );
                            ";
                    $html .= "</script>";

                }

            }

        }

        return $html;

    }

    private function listPendingRequests($group)
    {
        $requests = $group->memberRequests();
        $html = '';
        foreach($requests as $user) {
            $id = "user-id-" . $user->id;
            $html .= "<li class='groups-pending-request' id='$id'>";
            $html .= "<span class='groups-pending-request-user'>" . $user->name . "</span>";
            $html .= "<span class='groups-pending-request-view groups-button' >View</span>";
            $html .= "<span class='groups-pending-request-approve groups-button'>Approve</span>";
        }

        $html .= "<script type='text/javascript'>";
        $html .= "
                jQuery(document).ready(
                        jQuery('span.groups-pending-request-approve').click(Omeka.Groups.approveRequest)
                    );
                jQuery(document).ready(
                        jQuery('span.groups-pending-request-view').click(Omeka.Groups.viewRequest)
                    );



                ";
        $html .= "</script>";

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


