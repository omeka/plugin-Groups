<?php

$this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
echo head(array('title'=>$group->title));
?>


<div id='primary'>
    <?php echo flash(); ?>
    <?php if(is_allowed($group, 'edit')):?>
        <a href="<?php echo record_url($group, 'edit'); ?>">Edit</a>
    <?php endif; ?>

    <?php if(is_allowed($group, 'manage')):?>
        <a href="<?php echo record_url($group, 'manage'); ?>">Manage</a>
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
    <h2>Members (<?php echo metadata($group, 'members count');?>)</h2>
    <?php $owner = $group->findOwner(); ?>
    <?php if($owner->name) {
        $name = $owner->name;
    } else {
        $name = $owner->username;
    }     
    ?>
    <p id='groups-owner'>Owner: <?php echo $name; ?></p>
    <?php if(is_allowed($group, 'items')): ?>
    <ul class='groups-members'>
        <?php foreach($members as $member): ?>
            <li>
                <?php  
                    if(plugin_is_active('UserProfiles')) {
                        echo $this->linkToOwnerProfile(array('owner'=>$member->User, 'text'=> '(' . metadata($member, 'role') . ')' ));
                    } else {
                        if($member->User->name) {
                            echo $member->User->name;
                        } else {
                            echo $member->User->username;
                        }
                    }
                ?>
            </li>        
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    
    <!--  Items list -->
    <h2>Items (<?php echo groups_item_count($group); ?>)</h2>
    <?php if(is_allowed($group, 'items')): ?>
        
        <?php set_loop_records('item', groups_items_for_group()); ?>
        <?php foreach(loop('item') as $item): ?>
        <div class='groups-item'>
        <h2><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title')), array('class'=>'permalink')); ?></h2>
            <?php if(plugin_is_active('Sites')): ?>
            <div class="sites-site-title">
                <p>From <?php // echo sites_link_to_site_for_item(); ?></p>
            </div>
            <?php endif; ?>
        <?php if (item_has_thumbnail()): ?>
            <div class="item-img">
                <?php echo link_to_item(item_square_thumbnail()); ?>
            </div>
        <?php endif; ?>
            <div class="groups-comments">
                <?php $item = get_current_item(); ?>
                <?php echo CommentingPlugin::showComments(array('comments'=>$group->getComments($item))); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php echo foot(); ?>