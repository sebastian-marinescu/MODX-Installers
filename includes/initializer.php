<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/configcheck.php');

// get some defaults
$projectname = getopt("p:");
if(!isset($projectname) || empty($projectname)) {
    $projectname = readQuestion('Please enter a project folder (relative to: '.$_config['projectsPath'].')', 'any');
    if(empty($projectname)) {
        die(date('Y-m-d H:i:s').' [INIT ERROR] Please specify a project directory... Installer aborted, try again!'."\n");
    }
}

$projectpath = $_config['projectsPath'].rtrim($projectname, '/').'/';
if(!isset($isNewInstall) || $isNewInstall === false) { // add new installs; below cannot be initialized!!

    if(!file_exists($projectpath)) {
        die(date('Y-m-d H:i:s').' [INIT ERROR] Project directory "'.$projectpath.'" doesn\'t exists... Installer aborted, try again!'."\n");
    }

    define('MODX_API_MODE', true);

    /* this can be used to disable caching in MODX absolutely */
    $modx_cache_disabled = false;

    /* include custom core config and define core path */
    include($projectpath.'config.core.php');
    if (!defined('MODX_CORE_PATH')) {
        die(date('Y-m-d H:i:s').' [INIT ERROR] MODX_CORE_PATH not set after including "'.$projectpath.'config.core.php"... Installer aborted, try again!'."\n");
    }

    /* include the modX class */
    if (!@include_once (MODX_CORE_PATH . "model/modx/modx.class.php")) {
        die(date('Y-m-d H:i:s').' [ERROR 503] MODX core cannot be loaded... Installer aborted, try again!'."\n");
    }

    /** @var modX $modx An instance of the MODX class */
    $modx = new modX();
    if (!is_object($modx) || !($modx instanceof modX)) {
        @ob_end_flush();
        die(date('Y-m-d H:i:s').' [ERROR 503] MODX core cannot be loaded, is it installed at all? Installer aborted, try again!'."\n");
    }

    $modx->initialize('mgr');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

    $modx->log(modX::LOG_LEVEL_INFO, 'MODX initialized... Installer will continue...');
}