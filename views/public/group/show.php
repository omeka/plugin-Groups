<?php
head(array());
?>


<div id='primary'>
    <h1><?php echo $group->title; ?></h1>
    <?php echo flash(); ?>
    <?php if(has_permission($group, 'edit')):?>
        <a href="<?php echo record_uri($group, 'edit'); ?>">Edit</a>
    <?php endif; ?>

    <?php if(has_permission($group, 'manage')):?>
        <a href="<?php echo record_uri($group, 'manage'); ?>">Manage</a>
    <?php endif; ?>    
    
    <p class='groups-type'>Type: <?php echo groups_group('visibility'); ?>
    <?php echo groups_group_visibility_text(); ?>
    </p>
    <div class='groups-description'><?php echo $group->description; ?></div>
    <div class='groups-tags'>
        <?php echo groups_tags_string_for_group($group); ?>            
    </div>

    <!--  Members list -->
    <?php $members = groups_get_memberships($group); ?>
    <h2>Members (<?php echo groups_member_count($group); ?>)</h2>
    <?php $owner = $group->findOwner(); ?>
    <?php if($owner->name) {
        $name = $owner->name;
    } else {
        $name = $owner->username;
    }     
    ?>
    <p id='groups-owner'>Owner: <?php echo $name; ?></p>
    <?php if(has_permission($group, 'items')): ?>
    <ul class='groups-members'>
        <?php foreach($members as $member): ?>
            <li>
                <?php  
                    if(plugin_is_active('UserProfiles')) {
                        user_profiles_link_to_profile($member->User, $member->User->name);
                    } else {
                        if($member->User->name) {
                            echo $member->User->name;
                        } else {
                            echo $member->User->username;
                        }
                        
                    }
                ?>: <?php echo $member->role(); ?>
            
            </li>        
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    
    
    <!--  Items list -->
    <h2>Items (<?php echo groups_item_count($group); ?>)</h2>
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