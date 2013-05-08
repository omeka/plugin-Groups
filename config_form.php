
<div class="field">
    <div class="two columns alpha">
        <label><?php echo __("Make groups taggable"); ?></label>    
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("If checked, group administrators can add descriptive tags to groups."); ?></p>
        <div class="input-block">        
        <?php 
        $view = get_view();
        echo $view->formCheckbox('groups_taggable', null,
                array('checked'=> (bool) get_option('commenting_threaded') ? 'checked' : ''
                )
            ); 
        ?>
        </div>
    </div>
</div>


