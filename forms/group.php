<?php


class GroupForm extends Omeka_Form
{

    public function init()
    {
        parent::init();
        $this->addElement('text', 'title', array('label'=>'Group Title'));
        $this->addElement('textarea', 'description', array('label'=>'Description'));
        $this->addElement('text', 'tags', array('label'=>'Tags'));
        $options = array('open'=>'Open: Membership is open. Viewing items and discussion is public.',
            'closed'=>'Closed: Membership requires owner approval. Viewing items and discussion is restricted to members.',
            'public'=>'Public: Membership requires owner approval. Viewing items and discussion is public.');

        $this->addElement('select', 'visibility', array('label'=>'Visibility', 'multiOptions'=>$options));
        $this->addElement('submit', 'submit');
    }




}