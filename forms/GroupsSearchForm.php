<?php

class GroupsSearchForm extends Omeka_Form
{

    public function init()
    {
        parent::init();
        $this->addElement('text', 'groupsSearch', array('label'=>'Search Groups:'));
        $this->addElement('submit', 'submit');
    }


}