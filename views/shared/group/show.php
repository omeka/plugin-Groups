<?php
head(array());
?>


<div id='primary'>
    <h1><?php echo $group->title; ?></h1>
    <?php echo groups_tags_for_group($group); ?>
    <p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
    <p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
</div>


<?php foot(); ?>