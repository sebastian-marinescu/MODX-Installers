<?php

/**
 * Ask the user an answer to continue or not
 * @param $question The question to ask user
 * @param string $type Type of question, supports "yesno" or "any"
 * @param boolean $returnLower Whether or not to return the entered value lowercased
 * @return bool|string
 */
function readQuestion($question, $type='yesno', $returnLower=false) {

    echo trim($question).(($type == 'yesno') ? ' (Y/n)' : '').': ';

    $handle = fopen ("php://stdin","r");
    $line = trim(fgets($handle));

    switch($type) {
        case 'any':
            if($returnLower) {
                return strtolower($line);
            }
            return $line;
        break;

        case 'yesno':
        default:
            if(strtolower(trim($line)) == 'n'){
                return false;
            }
        break;
    }
    return true;
}

/**
 * Will generate a new password
 * @param int $length The length of the password, default 8
 * @return string
 */
function generatePassword($length=12) {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!@#$&()[]{}~";
    $pass = array(); // remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; // put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); // turn the array into a string
}

/**
 * Will include/run/process PHP files as hooks on several places
 * @param string $topic The topic-name to run hooks from
 * @param string $suffix The ending of the hook filename
 * @return bool
 */
function loadHooks($topic, $suffix='.php') {
    global $modx;

    if(empty($topic)) { return false; }
    foreach(glob(dirname(dirname(__FILE__)).'/hooks/'.$topic.'.*'.$suffix) as $file) {
        $hookName = str_replace(array($topic.'.', $suffix), '', basename($file));
        echo date('Y-m-d H:i:s').' [INFO] Running your "'.$hookName.'" hook...'."\n";
        require_once($file);
    }
    return true;
}