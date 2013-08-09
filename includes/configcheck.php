<?php

// read config
$_configFile = dirname(__FILE__).'/config.conf';
if(!file_exists($_configFile)) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] The "includes/config.conf" file doesn\'t exists... Please copy the sample and change the settings values!'."\n");
}

// read config
$_config = parse_ini_file($_configFile, true);

if(empty($_config)) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Your "includes/config.conf" is totally empty... do copy the sample contents and change the values!'."\n");
}

// check mysql config
if(!isset($_config['mysql']) || empty($_config['mysql'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Cannot read any [mysql] section in your config... Please copy the example and change the mysql values!'."\n");
}

if(empty($_config['mysql']) || empty($_config['mysql']['USER'])) {
    die(date('Y-m-d H:i:s').' [ERROR] Cannot load your MySQL config in "includes/config.conf"... Please copy the example and enter your credentials!'."\n");
}

// check paths
if(!isset($_config['paths']) || empty($_config['paths'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Cannot read any [paths] section in your config... Please copy the example and change the path values!'."\n");
}

if(!isset($_config['paths']['PROJECTS']) || empty($_config['paths']['PROJECTS']) || !is_dir($_config['paths']['PROJECTS'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Cannot read "'.$_config['paths']['PROJECTS'].'"... Create it or change PROJECTS path to an accessible directory!'."\n");
}

if(strtolower($_config['apacheCreateVHS']) == 'yes') {

    if(!isset($_config['paths']['VHS']) || empty($_config['paths']['VHS']) || !is_dir($_config['paths']['VHS'])) {
        die(date('Y-m-d H:i:s').' [CONFIG ERROR] Cannot read "'.$_config['paths']['VHS'].'" path... Create it or change VHS path to an accessible directory!'."\n");
    }

    if(!is_writable($_config['paths']['VHS'])) {
        die(date('Y-m-d H:i:s').' [CONFIG ERROR] The "'.$_config['paths']['VHS'].'" path is not writable for PHP command!'."\n");
    }
}