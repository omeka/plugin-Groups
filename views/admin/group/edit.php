<?php
echo head(array('title'=>'Edit Group', 'bodyclass'=>'groups edit'));
?>


<form method="post" enctype="multipart/form-data" id="group-form" action="">

<section class="seven columns alpha">
<div class="field">
    <div class="two columns alpha">
        <label>Title</label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"></p>
        <div class="input-block">
            <?php echo $this->formText('title', $group->title ); ?>
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
            <?php echo $this->formTextarea('description', $group->description ); ?>
        </div>
    </div>
</div>

</section>

<section class="three columns omega">
    <div id="save" class="panel">
        <?php echo $this->formSubmit('submit', __('Save Changes'), array('id'=>'save-changes', 'class'=>'submit big green button')); ?>
    </div>

    <div id="public-featured">
        <div class="public">
            <label for="public"><?php echo __('Public'); ?>:</label>
            <?php echo $this->formCheckbox('public', $group->public, array(), array('1', '0')); ?>
        </div>
        <div class="featured">
            <label for="featured"><?php echo __('Featured'); ?>:</label>
            <?php echo $this->formCheckbox('featured', $group->featured, array(), array('1', '0')); ?>
        </div>
    </div> <!-- end public-featured  div -->

</section>
</form>
<?php echo foot(); ?>