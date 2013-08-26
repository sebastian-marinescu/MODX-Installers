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
if(!isset($adminUser) || empty($adminUser)) {
    $adminUser = readQuestion('Enter the admin MODX Manager username', 'any');
}

// MODX admin password
$adminPassword = $_config['modx']['password'];
if(!isset($adminPassword) || empty($adminPassword)) {
    echo date('Y-m-d H:i:s').' [INFO] MODX password found empty in config... Generating new password!'."\n";
    $adminPassword = generatePassword();
}

// MODX admin email
$adminEmail = $_config['modx']['email'];
if(!isset($adminEmail) || empty($adminEmail)) {
    $adminEmail = readQuestion('Enter the admin MODX Manager emailaddress', 'any');
}

// Creating project root path
echo date('Y-m-d H:i:s').' [INFO] Creating project directory...'."\n";
mkdir($projectpath, 0755, true);
chdir($projectpath); // moves PHP to project path
$out = exec('cd '.$projectpath); // moves environment to project path

// whether or not to create VHS
if(strtolower($_config['apacheCreateVHS']) == 'yes') {

    // create virtualhost file
    echo date('Y-m-d H:i:s').' [INFO] Creating VirtualHost file...'."\n";

    $vhfileContents = "<VirtualHost *:80>\n
\tServerName {$projecthost}\n".
((!empty($projectalias)) ? "\tServerAlias {$projectalias}\n" : '')
."\tDocumentRoot {$projectpath}\n
</VirtualHost>";

    $hostsfile = $_config['paths']['VHS'].$projecthost;
    $idx = 1;
    while(file_exists($hostsfile)) {
        $hostsfile = $_config['paths']['VHS'].$projecthost.'.'.$idx;
        $idx++;
    }

    $fh = fopen($hostsfile, "w+");
    fwrite($fh, $vhfileContents);
    fclose($fh);

    // time to reload Apache
    echo date('Y-m-d H:i:s').' [INFO] Reloading Apache...'."\n";
    if(isset($_config['apacheReloadCommand']) && !empty($_config['apacheReloadCommand'])) {
        $out = exec($_config['apacheReloadCommand']);
    } else {
        $out = exec('/etc/init.d/apache2 reload');
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