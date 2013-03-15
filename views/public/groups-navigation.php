<nav id="section-nav" class="navigation vertical">
<?php 

$navArray = array(
        'Create' => array( 'label'=>__('Create a group') , 'uri'=>url('groups/add') ),
        'My Groups' => array('label'=>__('My groups'), 'uri'=>url('groups/my-groups')),
        'Admin' => array('label'=>__('Administration'), 'uri'=>url('groups/administration')),
        'Invite' => array('label'=>__('Invite others'), 'uri'=>url('groups/invitations'))
        );

echo nav($navArray, 'groups_navigation');

?>
</nav>
