<?php

require_once(dirname(__FILE__).'/includes/initializer.php');

/* Settings */
// TODO: make options array functional for packages having setup options
$installPackages = array(
	'Ace' => array(),
	'TinyMCE' => array(),
    'Wayfinder' => array(),
    'getResources' => array(),
    'FormIt' => array(),
    'Breadcrumbs' => array(),
    'getPage' => array(),
    'Copyright' => array(),
    'GoogleSiteMap' => array(),
    'Gallery' => array(),
    'getCache' => array(),
    'phpThumbOf' => array(),
    'Redirector' => array(),
    'SimpleSearch' => array(),
    'UltimateParent' => array(),

    /** specials **/
    'SimpleCart' => array(
        '_provider' => array(
            'name' => 'modxsimplecart.com',
            'service_url' => 'http://rest.modxsimplecart.com/',
            'username' => '',
            'api_key' => '',
            'description' => 'The MODX SimpleCart package provider',
        ),
    ),
    'Redactor' => array(
        '_provider' => array(
            'name' => 'modmore.com',
            'service_url' => 'https://rest.modmore.com/',
            'username' => '',
            'api_key' => '',
            'description' => 'The modmore package provider',
        ),
    ),
);

// add transport package
$modx->addPackage('modx.transport', $projectpath.'/core/model/');

/** @var modTransportProvider $provider */
$defaultProvider = $modx->getObject('transport.modTransportProvider', 1);
$defaultProvider->getClient();
$modx->getVersionData();
$productVersion = $modx->version['code_name'].'-'.$modx->version['full_version'];

foreach($installPackages as $packageName => $installOptions) {

    $installIt = readQuestion('Do you want to install "'.$packageName.'"?');
    if(!$installIt) { continue; }

    $completed = downloadAndInstallPackage($packageName, $installOptions);
    if(!$completed) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Package '.$packageName.' not found');
        continue;
    }
}

// custom add another packages
$extraPackage = readQuestion('Add another package? Type the name (or leave empty to stop)', 'any');
while(!empty($extraPackage)) {
    $completed = downloadAndInstallPackage($extraPackage);
    if(!$completed) {
        $modx->log(modX::LOG_LEVEL_ERROR, 'Package '.$extraPackage.' not found');
    }
    $extraPackage = readQuestion('Add another package? Type the name (or leave empty to stop)', 'any');
}

/**
 * Downloads and installs packages
 * @param $packageName
 * @param array $installOptions
 * @return bool
 */
function downloadAndInstallPackage($packageName, $installOptions=array()) {

    global $modx, $defaultProvider, $productVersion;

    // setting back default provider each time because it can be overwritten
    $provider = $defaultProvider;

    // check if there isset another provider setting
    if(isset($installOptions['_provider']) && !empty($installOptions['_provider'])) {
        // check if all options are set well
        $params = array_merge(array('name' => '', 'service_url' => '', 'username' => '', 'api_key' => '', 'description' => ''), $installOptions['_provider']);

        // check if it exists already
        $provider = $modx->getObject('transport.modTransportProvider', array('name' => $params['name']));
        if(empty($provider) || !is_object($provider)) {

            foreach($params as $key => $value) {
                if(empty($value)) {
                    $newVal = readQuestion('Please enter provider setting "'.$key.'" for package "'.$packageName.'"', 'any');
                    if(empty($newVal) && $key != 'description') {
                        $modx->log(modX::LOG_LEVEL_ERROR, 'Cannot continue adding package "'.$packageName.'"! Reason: missing provider settings! SKIPPED...');
                        return false;
                    }
                }
            }

            // create provider
            $provider = $modx->newObject('transport.modTransportProvider', $params);
            if(!$provider->save()) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'Cannot continue adding package "'.$packageName.'"! Reason: provider cannot be initialized...');
                return false;
            }
        }

        if($provider->verify() !== true) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Cannot continue adding package "'.$packageName.'"! Reason: provider cannot be verified...');
            $error = $provider->verify();
            if(!empty($error) && is_string($error)) {
                $modx->log(modX::LOG_LEVEL_ERROR, 'PROVIDER SAYS: '.$error);
            }
            return false;
        }

        $provider->getClient();
    }

    // continue install
    $modx->log(modX::LOG_LEVEL_INFO, 'Heading to install '.$packageName.'...');

    $response = $provider->request('package','GET', array(
        'supports' => $productVersion,
        'query' => $packageName
    ));

    if(!empty($response)) {
        $foundPackages = simplexml_load_string($response->response);

        foreach($foundPackages as $foundPackage) {
            if($foundPackage->name == $packageName) {
                /* define version */
                $sig = explode('-',$foundPackage->signature);
                $versionSignature = explode('.',$sig[1]);

                //download file
                file_put_contents(
                    $modx->getOption('core_path').'packages/'.$foundPackage->signature.'.transport.zip',
                    file_get_contents($foundPackage->location)
                );

                /* add in the package as an object so it can be upgraded */
                /** @var modTransportPackage $package */
                $package = $modx->newObject('transport.modTransportPackage');
                $package->set('signature',$foundPackage->signature);
                $package->fromArray(array(
					'created' => date('Y-m-d h:i:s'),
					'updated' => null,
					'state' => 1,
					'workspace' => 1,
					'provider' => 1,
					'source' => $foundPackage->signature.'.transport.zip',
					'package_name' => $packageName,
					'version_major' => $versionSignature[0],
					'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
					'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
                ));
                if (!empty($sig[2])) {
                    $r = preg_split('/([0-9]+)/',$sig[2],-1,PREG_SPLIT_DELIM_CAPTURE);
                    if (is_array($r) && !empty($r)) {
                        $package->set('release',$r[0]);
                        $package->set('release_index',(isset($r[1]) ? $r[1] : '0'));
                    } else {
                        $package->set('release',$sig[2]);
                    }
                }
                $success = $package->save();
                if($success) {
                    $package->install();
                }
                else {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not save package '.$foundPackage->name);
                }
                break;
            }
        }

        return true;
    }

    return false;
}

loadHooks('install-packages');

$modx->log(modX::LOG_LEVEL_INFO, 'DONE!');