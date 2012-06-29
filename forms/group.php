<?php


class GroupForm extends Omeka_Form
{

    public function init()
    {
        parent::init();
        $this->addElement('text', 'title', array('label'=>'Group Title'));
        $this->addElement('textarea', 'description', array('label'=>'Description'));
        $this->addElement('text', 'tags', array('label'=>'Tags'));
        $options = array('open'=>'Open -- Anyone may join and see all items',
            'closed'=>'Closed -- Approval is required to join; items only visible to members',
            'public'=>'Public -- Anyone can see items, but approval is required to join');

        $this->addElement('select', 'visibility', array('label'=>'Visibility', 'multiOptions'=>$options));
        $this->addElement('submit', 'submit');
    }
}