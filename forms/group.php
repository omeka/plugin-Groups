<?php


class GroupForm extends Omeka_Form
{

    public function init()
    {
        parent::init();
        $this->addElement('text', 'title', array('label'=>'Group Title'));
        $this->addElement('textarea', 'description', array('label'=>'Description'));
        $this->addElement('text', 'tags', array('label'=>'Tags'));
        $this->addElement('select', 'visibility', array('label'=>'Visibility', 'multiOptions'=>array('open'=>'Open', 'closed'=>'Closed', 'public'=>'Public')));


        $this->addElement('submit', 'submit');
    }




}