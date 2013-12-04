<?php
echo head(array('title'=>$group->title, 'bodyclass'=>'groups show'));
?>


<section class="seven columns alpha">
    <?php echo flash(); ?>
    <div class="field">
        <div class="two columns alpha">
            <label>Title</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"></p>
            <div class="input-block">
                <?php echo $group->title; ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <label>Description</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation"></p>
            <div class="input-block">
                <?php echo $group->description; ?>
            </div>
        </div>
    </div>
</section>
<section class="three columns omega">

    <div id="edit" class="panel">
        <a class='big blue button' href="<?php echo url('groups/group/edit/id/' . $group->id); ?>">Edit</a>
    </div>

    <div class="public-featured panel">
        <p><span class="label"><?php echo __('Public'); ?>:</span> <?php echo ($group->public) ? __('Yes') : __('No'); ?></p>
        <p><span class="label"><?php echo __('Featured'); ?>:</span> <?php echo ($group->featured) ? __('Yes') : __('No'); ?></p>
    </div>

</section>

<?php
echo foot();
?>