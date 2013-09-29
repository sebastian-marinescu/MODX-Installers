<?php

// read config
$_configFile = dirname(__FILE__).'/config.conf';
if(!file_exists($_configFile)) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] The "includes/config.conf" file doesn\'t exists... Please copy the sample and change the settings values!'."\n");
}

// read config
$_config = parse_ini_file($_configFile, true);
if(empty($_config)) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Your "includes/config.conf" is totally empty... please copy the sample config and change values where needed!'."\n");
}

if(!isset($_config['apacheCreateVHS']) || empty($_config['apacheCreateVHS'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Apache Create VHS directive is missing... please copy the sample config and change values where needed!'."\n");
}
if(!isset($_config['nginxCreateVHS']) || empty($_config['nginxCreateVHS'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] NginX Create VHS directive is missing or empty... please copy the sample config and change values where needed!'."\n");
}
if(!isset($_config['projectsPath']) || empty($_config['projectsPath'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] Projects container path is missing or empty... please copy the sample config and change values where needed!'."\n");
}

if(!isset($_config['modx']) || !isset($_config['modx']['username']) || !isset($_config['modx']['password']) || !isset($_config['modx']['email'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] MODX block (or it\'s values) is missing... please copy the sample config and change values where needed!'."\n");
}

if($_config['apacheCreateVHS'] == 'yes' && (!isset($_config['apache']) || !isset($_config['apache']['ACTIVE']) || !isset($_config['apache']['VHS']) || !isset($_config['apache']['RELOAD_CMD']))) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] APACHE block (or it\'s values) is missing... please copy the sample config and change values where needed!'."\n");
}
if($_config['nginxCreateVHS'] == 'yes' && (!isset($_config['nginx']) || !isset($_config['nginx']['ACTIVE']) || !isset($_config['nginx']['VHS']) || !isset($_config['nginx']['RELOAD_CMD']))) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] NGINX block (or it\'s values) is missing... please copy the sample config and change values where needed!'."\n");
}

if(!isset($_config['mysql']) || !isset($_config['mysql']['HOST']) || !isset($_config['mysql']['USER']) || !isset($_config['mysql']['PASS'])) {
    die(date('Y-m-d H:i:s').' [CONFIG ERROR] MYSQL block (or it\'s values) is missing... please copy the sample config and change values where needed!'."\n");
}