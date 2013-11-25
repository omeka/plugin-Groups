<?php

class Group_View_Helper_GroupAddItem extends Zend_View_Helper_Abstract
{
    protected $_db;
    protected $_groups = array();
    protected $_item;
    protected $_user;

    public function groupAddItem($options = array())
    {
        $this->_setOptions($options);
        if(!$this->_user || empty($this->_groups) || !$this->_item) {
            return;
        }
        $html = "<div class='groups-add-item'>";
        $html .= "<h2>" . metadata($this->_item, array('Dublin Core', 'Title')) . " groups</h2>";
        $html .= "<ul id='groups-add-item'>";
        foreach($this->_groups as $group) {
            if($group->hasItem($this->_item)) {
                if(is_allowed($group, 'remove-item')) {
                    $removeLink = "<span id='groups-remove-id-{$group->id}' class='groups-item-remove'>Remove</span>";
                } else {
                    $removeLink = '';
                }
                $html .= "<li id='groups-id-{$group->id}' class='groups-item-exists'>" . link_to($group, 'show', $group->title) . $removeLink .  "</li>";
            } else {
                $html .= "<li id='groups-id-{$group->id}' class='groups-item-add'>{$group->title}</li>";
            }
        }
        $html .= "</ul></div>";
        return $html;
    }

    protected function _setOptions($options)
    {
        $this->_db = get_db();
        $this->_setUser($options);
        $this->_setItem($options);
        $this->_setGroups($options);
    }

    protected function _setUser($options)
    {
        if(isset($options['user'])) {
            $this->_user = $options['user'];
        } else {
            $this->_user = current_user() ? current_user() : false;
        }
    }

    protected function _setGroups($options)
    {
        if(!$this->_user) {
            $this->_groups = array();
        }
        $options['is_pending'] = false;
        $this->_groups =  $this->_db->getTable('GroupMembership')->findGroupsBy($options);
    }

    protected function _setItem($options)
    {
        if(isset($options['item'])) {
            $this->_item = $options['item'];
        } else if($item = get_view()->item) {
            $this->_item = $item;
        } else {
            $this->_item = false; // this should probably throw an exception
        }
    }
}