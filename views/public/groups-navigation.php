<nav id="section-nav" class="navigation vertical groups-section-nav">
<?php 
$navArray = array(
        'Create' => array( 'label'=>__('Create a group') , 'uri'=>url('groups/add') ),
        'Admin' => array('label'=>__('Administer group members'), 'uri'=>url('groups/administration')),
        'Invite' => array('label'=>__('Invite other to groups'), 'uri'=>url('groups/invitations'))
        );
echo nav($navArray, 'groups_navigation');
?>
</nav>
