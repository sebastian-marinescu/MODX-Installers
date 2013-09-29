<?php
/**
 * MODX CORE INSTALLER
 *
 * @author Bert Oost at OostDesign.com <bert@oostdesign.com>
 */

$isNewInstall = true;
require_once(dirname(__FILE__) . '/includes/initializer.php');

// ask the necessary data
$_config['mysql']['NAME'] = readQuestion('Enter the database name', 'any');
$projecthost = readQuestion('Enter project host (eq. x.yourname.com)', 'any');
$projectalias = readQuestion('Enter project alias (eq. x.yourname.nl)', 'any');

// MODX admin user
$adminUser = $_config['modx']['username'];
$adminUser = (!empty($adminUser)) ? $adminUser : 'admin';

// MODX admin password
$adminPassword = $_config['modx']['password'];
$adminPassword = (!empty($adminPassword)) ? $adminPassword : generatePassword();

// MODX admin email
$adminEmail = $_config['modx']['email'];
//if(!isset($adminEmail) || empty($adminEmail)) {
while(empty($adminEmail)) {
    $adminEmail = readQuestion('Please enter the MODX Manager emailaddress', 'any');
}

// Creating project root path
echo date('Y-m-d H:i:s').' [INFO] Creating project directory...'."\n";
mkdir($projectpath, 0755, true);
chdir($projectpath); // moves PHP to project path
$out = exec('cd '.$projectpath); // moves environment to project path

// --------------------------
// APACHE VHS
if(strtolower($_config['apacheCreateVHS']) == 'yes') {

    // create virtualhost file
    echo date('Y-m-d H:i:s').' [INFO] Creating Apache VirtualHost file...'."\n";

    $tplFile = dirname(__FILE__).'/templates/vhs.apache.tpl';
    if(!empty($projectalias)) {
        $tplFile = dirname(__FILE__).'/templates/vhs.apache.alias.tpl';
    }

    if(file_exists($tplFile)) {
        $vhfileContents = file_get_contents($tplFile);
        $vhfileContents = str_replace('{projecthost}', $projecthost, $vhfileContents);
        $vhfileContents = str_replace('{projectalias}', $projectalias, $vhfileContents);
        $vhfileContents = str_replace('{projectpath}', $projectpath, $vhfileContents);
    }
    else {
        $vhfileContents = "<VirtualHost *:80>\n
\tServerName {$projecthost}\n".
((!empty($projectalias)) ? "\tServerAlias {$projectalias}\n" : '')
."\tDocumentRoot {$projectpath}\n
</VirtualHost>";
    }

    $hostsfile = $_config['apache']['VHS'].$projecthost;
    $idx = 1;
    while(file_exists($hostsfile)) {
        $hostsfile = $_config['apache']['VHS'].$projecthost.'.'.$idx;
        $idx++;
    }

    $fh = fopen($hostsfile, "w+");
    fwrite($fh, $vhfileContents);
    fclose($fh);

    // time to reload Apache
    if(isset($_config['apache']['ACTIVE']) && $_config['apache']['ACTIVE'] == 'yes') {
        echo date('Y-m-d H:i:s').' [INFO] Reloading Apache...'."\n";
        if(isset($_config['apache']['RELOAD_CMD']) && !empty($_config['apache']['RELOAD_CMD'])) {
            $out = exec($_config['apache']['RELOAD_CMD']);
        } else {
            echo date('Y-m-d H:i:s').' [ERROR] Unable to reload Apache, please reload it yourself...'."\n";
        }
    }
}

// --------------------------
// NGINX VHS
if(strtolower($_config['nginxCreateVHS']) == 'yes') {

    // create virtualhost file
    echo date('Y-m-d H:i:s').' [INFO] Creating NginX server block (virtualhost) file...'."\n";

    $tplFile = dirname(__FILE__).'/templates/vhs.nginx.tpl';
    if(!empty($projectalias)) {
        $tplFile = dirname(__FILE__).'/templates/vhs.nginx.alias.tpl';
    }

    if(file_exists($tplFile)) {
        $vhfileContents = file_get_contents($tplFile);
        $vhfileContents = str_replace('{projecthost}', $projecthost, $vhfileContents);
        $vhfileContents = str_replace('{projectalias}', $projectalias, $vhfileContents);
        $vhfileContents = str_replace('{projectpath}', $projectpath, $vhfileContents);
    }
    else {
        $vhfileContents = "server {\n
\tlisten 80;
\tserver_name {$projecthost}".((!empty($projectalias)) ? " {$projectalias}" : '').";\n
\troot {$projectpath};\n

\tlocation / {
\t\tif (!-e $request_filename) {
\t\t\trewrite ^/(.*)$ /index.php?q=$1 last;
\t\t}
\t}
}";
    }

    $hostsfile = $_config['nginx']['VHS'].$projecthost;
    $idx = 1;
    while(file_exists($hostsfile)) {
        $hostsfile = $_config['nginx']['VHS'].$projecthost.'.'.$idx;
        $idx++;
    }

    $fh = fopen($hostsfile, "w+");
    fwrite($fh, $vhfileContents);
    fclose($fh);

    // time to reload Apache
    if(isset($_config['nginx']['ACTIVE']) && $_config['nginx']['ACTIVE'] == 'yes') {
        echo date('Y-m-d H:i:s').' [INFO] Reloading NginX...'."\n";
        if(isset($_config['nginx']['RELOAD_CMD']) && !empty($_config['nginx']['RELOAD_CMD'])) {
            $out = exec($_config['nginx']['RELOAD_CMD']);
        } else {
            echo date('Y-m-d H:i:s').' [ERROR] Unable to reload NginX, please reload it yourself...'."\n";
        }
    }
}

// Creating config xml to install MODX with
echo date('Y-m-d H:i:s').' [INFO] Creating MODX install config XML...'."\n";
$configXMLContents = "<modx>
    <database_type>mysql</database_type>
    <database_server>localhost</database_server>
    <database>{$_config['mysql']['NAME']}</database>
    <database_user>{$_config['mysql']['USER']}</database_user>
    <database_password>{$_config['mysql']['PASS']}</database_password>
    <database_connection_charset>utf8</database_connection_charset>
    <database_charset>utf8</database_charset>
    <database_collation>utf8_general_ci</database_collation>
    <table_prefix>modx_</table_prefix>
    <https_port>443</https_port>
    <http_host>{$projecthost}</http_host>
    <cache_disabled>0</cache_disabled>
    <inplace>1</inplace>
    <unpacked>0</unpacked>
    <language>en</language>
    <cmsadmin>{$adminUser}</cmsadmin>
    <cmspassword>{$adminPassword}</cmspassword>
    <cmsadminemail>{$adminEmail}</cmsadminemail>
    <core_path>{$projectpath}core/</core_path>
    <context_mgr_path>{$projectpath}manager/</context_mgr_path>
    <context_mgr_url>/manager/</context_mgr_url>
    <context_connectors_path>{$projectpath}connectors/</context_connectors_path>
    <context_connectors_url>/connectors/</context_connectors_url>
    <context_web_path>{$projectpath}</context_web_path>
    <context_web_url>/</context_web_url>
    <remove_setup_directory>1</remove_setup_directory>
</modx>";

$fh = fopen('config.xml', "w+");
fwrite($fh, $configXMLContents);
fclose($fh);

// Creating the new database
echo date('Y-m-d H:i:s').' [INFO] Creating database '.$_config['mysql']['NAME'].'...'."\n";
$out = exec("mysql --user={$_config['mysql']['USER']} --password={$_config['mysql']['PASS']} -e \"CREATE DATABASE IF NOT EXISTS {$_config['mysql']['NAME']} CHARACTER SET utf8 COLLATE utf8_general_ci;\"");

// Get latest MODX
echo date('Y-m-d H:i:s').' [INFO] Getting latest MODX version from modx.com...'."\n";
$out = exec('wget -O modx.zip http://modx.com/download/latest/');

echo date('Y-m-d H:i:s').' [INFO] Unzipping MODX package...'."\n";
$out = exec('unzip modx.zip');

$_ZDIR = exec('ls -F | grep "\/" | head -1');
if($_ZDIR == '/') {
    die(date('Y-m-d H:i:s').' [ERROR] Cannot find unzipped MODX folder...'."\n");
}
else {

    echo date('Y-m-d H:i:s').' [INFO] Moving unzipped files out of temporary directory...'."\n";
    $out = exec("mv ./{$_ZDIR}* .; rm -r ./{$_ZDIR}");

    if(@unlink('modx.zip')) {
        echo date('Y-m-d H:i:s').' [INFO] Removed downloaded zip file...'."\n";
    }

    echo date('Y-m-d H:i:s').' [INFO] Running into MODX setup...'."\n";
    $out = exec("cd setup/; php ./index.php --installmode=new --config={$projectpath}config.xml");
    $out = exec("cd {$projectpath}");

    echo date('Y-m-d H:i:s').' [INFO] Copying ht.access to .htaccess...'."\n";
    copy('ht.access', '.htaccess');

    if(@unlink('config.xml')) {
        echo date('Y-m-d H:i:s').' [INFO] Removed config XML file...'."\n";
    }

    // load hooks
    loadHooks('install-core');
}

echo "\n\n---------------------------\nCongrats! MODX is installed!\n";
echo "Please visit http://{$projecthost}/manager/ to login.\n";
echo "User: {$adminUser}\n";
echo "Pass: {$adminPassword}\n";
echo "---------------------------\n";