<?php

class Group_View_Helper_GroupMembers extends Zend_View_Helper_Abstract
{
    
    public function groupMembers($group, $options = array())
    {
        $params = array('group_id'=>$group->id);
        if(isset($options['is_pending'])) {
            $pending = true;
        } else {
            $pending = false;
        }
        $html = "<div class='group-members'>";
        $html .= "<h3>" . __('Groups') . "</h3>";
        foreach($group->getMembers(array('group_id'=>$group->id), $pending) as $user) {
            $html .= "<li>" . $user->name . "</li>";
        }
        
        $html .= "</div>";
        return $html;
    }
}