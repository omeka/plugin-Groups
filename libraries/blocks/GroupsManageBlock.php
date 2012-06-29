<?php


class GroupsManageBlock extends Blocks_Block_Abstract
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
        
        $html = "<p>Type: " . groups_group('visibility') . groups_group_visibility_text() . "</p>";
        if($group->hasMember($currUser) ) {
            if(has_permission($group, 'approve-request')) {
                if (groups_group('visibility') != 'open') {
                    $users = $group->memberRequests();
                    if(count($users) == 0) {
                        $html .= "<p>No pending membership requests</p>";
                    } else {
                        $html .= "<p>Pending Membership Requests</p>";
                        $html .= "<ul class='groups-pending-requests'>";
                        $html .= $this->listPendingRequests($group);
                        $html .= "</ul>";
                    }
                }
            } 
            if(has_permission($group, 'quit')) {
                $html .= "<p class='groups-quit-button groups-button' id='groups-id-{$group->id}'>Leave</p>";
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
                $html .= "<p class='groups-join-button groups-button' id='groups-id-{$group->id}'>Join</p>";
                $html .= "<script type='text/javascript'>";
                $html .= "
                        jQuery(document).ready(
                                jQuery('p.groups-join-button').click(Omeka.Groups.join)
                            );
                        ";
                $html .= "</script>";
            } else {
                if($group->hasPendingMember($currUser)) {
                    $html .= "<p class='groups-pending'>Membership request is pending</p>";

                } else {
                    $html .= "<p class='groups-request-button groups-button' id='groups-id-{$group->id}'>Request Membership</p>";
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

