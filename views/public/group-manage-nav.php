<?php 
    if(current_user()) {
        $navArray = array();
        if(isset($group)) {
            if(is_allowed($group, 'edit')) {
                $navArray['Edit Group'] = array('label'=>__('Edit'), 'uri' => record_url($group, 'edit'));
            }
            
            if(is_allowed($group, 'manage')) {
                $navArray['Manage Group'] = array('label'=>__('My Group Settings'), 'uri' => record_url($group, 'manage'));
            }
        }
        if (count($navArray) > 0) {
            echo '<nav class="group-manage navigation">';
            echo nav($navArray, 'group_manage_nav');
            echo '</nav>';
        }
    }
?>
