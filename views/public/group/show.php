<?php
queue_js_file('groups');
$this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
echo head(array('title'=>$group->title, 'bodyclass'=>'groups show'));
?>

<h1><?php echo metadata($group, 'title'); ?></h1>

<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>

<div id="sidebar">

    <p class='groups-type'>Type: <?php echo metadata($group, 'visibility'); ?>
    <?php echo $group->visibilityText(); ?>
    </p>

    <?php echo $this->manageGroup($group); ?>

    <p class="items"><span class="number"><?php echo metadata($group, 'items_count'); ?></span> items</p>

    <p class="members"><span class="number"><?php echo metadata($group, 'members count');?></span> members</p>

    <p class="description"><?php echo $group->description; ?></p>

    <?php if(get_option('groups_taggable')): ?>
    <p class="tags">
        Tags: 
        <span class="tag"><?php echo groups_tags_string_for_group($group); ?></span>
    </p>
    <?php endif; ?>

</div>

<div id="primary">
    
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
                    $member_name = ($membership->User->name) ? $membership->User->name : $membership->User->username;
                    $alt_text = $member_name . ' (' . metadata($membership, 'role') . ')';
                    $gravatar_hash = md5(strtolower(trim($membership->User->email)));
                    $gravatar_url = "http://www.gravatar.com/avatar/$gravatar_hash";
                    $gravatar_tag = '<img src="' . $gravatar_url . '" title="' . $alt_text . '"' . ' alt="' . $alt_text . '">';
                    if(plugin_is_active('UserProfiles')) {
                        echo '<a href="' . url('user-profiles/profiles/user/id/' . $membership->User->id) . '">';
                        echo $gravatar_tag;
                        echo '</a>';
                    } else {
                        echo $gravatar_tag;
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
                <p>From <?php  echo sites_link_to_site_for_item(); ?></p>
            </div>
            <?php endif; ?>
        <?php echo item_image_gallery(array('wrapper'=>array('class'=>'item-images'))); ?>
            <div class="groups-comments">
                <?php $item = get_current_record('item'); ?>
                <?php // @TODO:  commenting integration is for Commons 2.0 echo CommentingPlugin::showComments(array('comments'=>$group->getComments($item))); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php echo foot(); ?>