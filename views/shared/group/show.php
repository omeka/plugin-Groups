<?php
head(array());
?>


<div id='primary'>
    <h1><?php echo $group->title; ?></h1>
    <?php if(has_permission('Groups_Group', 'edit')):?>
    <a href="<?php echo record_uri($group, 'edit'); ?>">Edit</a>
    <?php endif; ?>
    <?php echo groups_tags_for_group($group); ?>
    <p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
    <p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
    <?php if(has_permission('Groups_Group', 'items')): ?>
    <p>Items will go here</p>
    <?php else: ?>
    <p>Can't see me!</p>
    <?php endif; ?>
</div>


<?php foot(); ?>