<?php
queue_js_file('groups');
$this->addHelperPath(USER_PROFILES_DIR . '/helpers', 'UserProfiles_View_Helper_');
echo head(array('title'=>$group->title, 'bodyclass'=>'groups show'));
?>
<?php echo $this->partial('groups-navigation.php'); ?>

<h1><?php echo metadata($group, 'title'); ?></h1>

<?php echo $this->partial('group-manage-nav.php', array('group'=>$group)); ?>
<?php echo flash(); ?>

<div id="sidebar">

    <?php $visibility = metadata($group, 'visibility'); ?>
    <?php if($visibility !== "Open"): ?>
    <div class='groups-type <?php echo strtolower($visibility); ?>'>
        <span class="visibility" title="<?php echo $group->visibilityText(); ?>"><?php echo $visibility; ?></span>
        <span class="visibility-description"><?php echo $group->visibilityText(); ?></span>
    </div>
    <?php endif; ?>

    <?php echo $this->manageGroup($group); ?>

    <?php if($group->flagged) :?>
    <p class="groups-button flagged">Flagged</p>
    <?php else: ?>
    <p class="groups-button flag" id="group-<?php echo $group->id;?>">Flag Inappropriate</p>
    <?php endif; ?>

    <p class="items"><?php echo link_to_items_browse(metadata($group, 'items_count'), array('group_id' => $group->id), array('class' => 'number')); ?> items</p>

    <p class="members"><span class="number"><?php echo metadata($group, 'members count');?></span> members</p>

    <?php if(get_option('groups_taggable')): ?>
    <p class="tags">
        Tags:
        <span class="tag"><?php echo groups_tags_string_for_group($group); ?></span>
    </p>
    <?php endif; ?>

    <!--  Members list -->

    <div class="members">
        <?php $memberships = $group->getMemberships(); ?>
        <h2><?php echo __('Members'); ?> (<?php echo metadata($group, 'members count');?>)</h2>
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
    </div>

</div>

<div id="primary">

    <p class="description"><?php echo $group->description; ?></p>

</div>

<div id="recent-items" class="items-list with-images">
    <!--  Items list -->
    <h2><?php echo __('Recent items saved to ') . metadata($group, 'title'); ?> (<?php echo metadata($group, 'items count') . __(' total'); ?>)</h2>
    <?php if(is_allowed($group, 'items')): ?>
        <?php foreach(loop('item') as $item): ?>
        <div class='group item'>
            <?php $item_files = $item->getFiles(); ?>
            <?php foreach ($item_files as $item_file): ?>
                <?php $stop = 0; ?>
                <?php if ($item_file->has_derivative_image == 1): ?>
                    <div class="image" style="background-image: url('<?php echo file_display_url($item_file); ?>')"></div>
                    <?php $stop = 1; ?>
                <?php endif; ?>
                <?php if ($stop == 1) { break; } ?>
            <?php endforeach; ?>
            <?php if (count($item_files) < 1): ?>
                <div class="no image"></div>
            <?php endif; ?>

            <h3><?php echo link_to_item(metadata('item', array('Dublin Core', 'Title')), array('class'=>'permalink')); ?></h3>
            <?php if(plugin_is_active('Sites')): ?>
            <div class="sites-site-title">
                <p><?php echo sites_link_to_site_for_item($item); ?></p>
            </div>
            <?php endif; ?>
            <div class="groups-comments">
                <?php $item = get_current_record('item'); ?>
                <?php // @TODO:  commenting integration is for Commons 2.0 echo CommentingPlugin::showComments(array('comments'=>$group->getComments($item))); ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (metadata($group, 'items_count') > get_option('per_page_public')): ?>
        <p><?php echo link_to_items_browse(__('View All %s Items', metadata($group, 'items_count')), array('group_id' => $group->id), array('class' => 'view-all-items-link button')); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php echo foot(); ?>