<?php
class Group_View_Helper_GroupNotifications extends Zend_View_Helper_Abstract
{
    
    public function groupNotifications()
    {
        $user = current_user();
        if($user) {
            $html = '';
            $html .= "<div class='group-notifications'>";
            $html .= "<ul class='group-confirmations'>";
            //first, look up confirmations to become admin or owner
            $confirmations = get_db()->getTable('GroupConfirmation')->findBy(array('user_id'=>$user->id));
            if(empty($confirmations)) {
                return false;
            }
            foreach($confirmations as $confirmation) {
                $html .= '<li>';
                $group = $confirmation->Group;
                switch ($confirmation->type) {
                    case 'is_admin':
                        $role = __('an administrator');  
                    break;
                    case 'is_owner':
                        $role = __('an owner');
                    break;
                }
                $html .=  __("You have been asked to become $role for ") . link_to($group, 'manage', $group->title);
                $html .= '</li>';
            }
            $html .= '</ul>';
            
            $html .= "</div>";
            return $html;
        }
    }
    
    
    
}