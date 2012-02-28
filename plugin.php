<?php

define('GROUPS_PLUGIN_DIR', dirname(__FILE__));
require_once PLUGIN_DIR . '/RecordRelations/includes/models/RelatableRecord.php';
require_once GROUPS_PLUGIN_DIR . '/GroupsPlugin.php';
require_once GROUPS_PLUGIN_DIR . '/helpers/functions.php';

$gp = new GroupsPlugin();
$gp->setUp();


