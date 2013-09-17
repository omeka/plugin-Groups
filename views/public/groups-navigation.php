<nav class="groups-section-nav navigation">
<?php 
if(current_user()) {
    $navArray = array(
            'Create' => array( 'label'=>__('Create a group') , 'uri'=>url('groups/add') ),
            );
    if(is_allowed('Groups_Group', 'administration')) {
        $navArray['Admin'] = array('label'=>__('Administer groups'), 'uri'=>url('groups/administration'));
    }
    
    if(is_allowed('Groups_Group', 'invitations')) {
        $navArray['Invite'] = array('label'=>__('Invite other to groups'), 'uri'=>url('groups/invitations')); 
    }    
    echo nav($navArray, 'groups_navigation');
}
?>
</nav>
