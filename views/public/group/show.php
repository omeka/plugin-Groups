<?php

$this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
echo head(array('title'=>$group->title));
?>

<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>
<div id='primary'>
    
    <h1><?php echo $group->title;?></h1>
    <p class='groups-type'>Type: <?php echo metadata($group, 'visibility'); ?>
    <?php echo $group->visibilityText(); ?>
    </p>
    <div class='groups-description'><?php echo $group->description; ?></div>
    <div class='groups-tags'>
        <h2>Tags</h2>
        <?php echo groups_tags_string_for_group($group); ?>            
    </div>

    <!--  Members list -->
    <?php $memberships = $group->getMemberships(); ?>
    <h2>Members (<?php echo metadata($group, 'members count');?>)</h2>
    <?php $owner = $group->getOwner(); ?>
    <?php if($owner->name) {
        $name = $owner->name;
    } else {
        $name = $owner->username;
    }     
    ?>
    <p id='groups-owner'>Owner: <?php echo $name; ?></p>
    <?php if(is_allowed($group, 'items')): ?>
    <ul class='groups-members'>
        <?php foreach($memberships as $membership): ?>
            <li>
                <?php  
                    if(plugin_is_active('UserProfiles')) {
                        echo $this->linkToOwnerProfile(array('owner'=>$membership->User, 'text'=> '(' . metadata($membership, 'role') . ')' ));
                    } else {
                        if($membership->User->name) {
                            echo $membership->User->name;
                        } else {
                            echo $membership->User->username;
                        }
                    }
                ?>
            </li>        
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    
    <!--  Items list -->
    <h2>Items (<?php echo metadata($group, 'items_count'); ?>)</h2>
    <?php if(is_allowed($group, 'items')): ?>
        
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