<?php
head(array());
?>


<div id='primary'>
    <h1><?php echo $group->title; ?></h1>
    <?php if(has_permission($group, 'edit')):?>
        <a href="<?php echo record_uri($group, 'edit'); ?>">Edit</a>
    <?php endif; ?>

    <p class='groups-type'>Type: <?php echo groups_group('visibility'); ?>
    <?php echo groups_group_visibility_text(); ?>
    </p>
    <div class='groups-description'><?php echo $group->description; ?></div>
    <?php echo groups_tags_list_for_group($group); ?>
    <p id='groups-member-count'>Members: <?php echo groups_member_count($group); ?></p>
    <p id='groups-item-count'>Items: <?php echo groups_item_count($group); ?></p>
    <?php if(has_permission($group, 'items')): ?>
        <?php set_items_for_loop(groups_items_for_group()); ?>
        <?php while(loop_items()): ?>
        <div class='groups-item'>
        <h2><?php echo link_to_item(item('Dublin Core', 'Title'), array('class'=>'permalink')); ?></h2>
            <div class="sites-site-title">
                <p>From <?php echo sites_link_to_site_for_item(); ?></p>
            </div>
        <?php if (item_has_thumbnail()): ?>
        <div class="item-img">
            <?php echo link_to_item(item_square_thumbnail()); ?>
        </div>
        <?php endif; ?>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
    <div class="groupps-comments">
        <?php $comments = groups_comments_for_group($group); ?>
        <?php commenting_echo_comments(array('approved'=>true), $comments)?>
    </div>
</div>


<?php foot(); ?>