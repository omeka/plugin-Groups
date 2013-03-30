<?php

/*
 * Returns HTML for a block of typical notices and tasks for groups members
 */

class Group_View_Helper_ManageGroup extends Zend_View_Helper_Abstract
{
    
    public function manageGroup($group)
    {
        $user = current_user();
        $html = "<div class='groups-manage-group'>";
        $html .= "<h3>" . __('Manage group ') . metadata($group, 'title') . "</h3>";
        
        if($group->hasMember($user) ) {
            if(is_allowed($group, 'approve-request')) {
                if (metadata($group, 'visibility') != 'open') {
                    $users = $group->getMemberRequests();
                    if(count($users) == 0) {
                        $html .= "<p>No pending membership requests</p>";
                    } else {
                        $html .= "<p>Pending Membership Requests</p>";
                        $html .= "<ul class='groups-pending-requests'>";
                        $html .= $this->_listPendingRequests($group);
                        $html .= "</ul>";
                    }
                }
            }
            if(is_allowed($group, 'quit')) {
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
    
    protected function _listPendingRequests($group)
    {
        $requests = $group->getMemberRequests();
        $html = '';
        foreach($requests as $user) {
            $id = "user-id-" . $user->id;
            $html .= "<li class='groups-pending-request' id='$id'>";
            if(plugin_is_active('UserProfiles')) {
                $html .= "<a href='" . html_escape(PUBLIC_BASE_URL . "/user-profiles/profiles/user/id/{$user->id}") . "'>{$user->name}</a>";
            } else {
                $html .= "<span class='groups-pending-request-user'>" . $user->name . "</span>";
            }
    
            $html .= "<span class='groups-pending-request-approve groups-button'>Approve</span>";
        }
    
        $html .= "<script type='text/javascript'>";
        $html .= "
        jQuery(document).ready(
        jQuery('span.groups-pending-request-approve').click(Omeka.Groups.approveRequest)
        );
    
        ";
        $html .= "</script>";
    
        return $html;
    }    
    
}