<?php

class Group_View_Helper_GroupInvitations extends Zend_View_Helper_Abstract
{
    
    public function groupInvitations()
    {
        $html = "<ul class='group-invitations'>";
        $invitations = get_db()->getTable('GroupInvitation')->findBy(array('user_id'=>$user->id));
        foreach($invitations as $invitation) {
            $html .= '<li>';
            $html .= __("You have been invited to join ") . $invitation->Group->title;
            
            $html .= '</li>';
        }
        
        $html .= '</ul>';
    }
}