<?php

class GroupsSearchForm extends Omeka_Form
{

    public function init()
    {
        parent::init();
        $this->addElement('text', 'groupsSearch', array('label'=>'Search Terms:'));
        $this->addElement('submit', 'submit');
    }


}