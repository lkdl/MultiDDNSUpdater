<?php

/**
 * This is the main entry point of the updater.
 *
 * Every request should include the following parameters:
 *
 *     1. secret: The secret key as specified in the configuration
 *     2. ip: The new ip
 *
 * The updater performs an update for each configured host and returns
 * HTTP status code 200 if *all* updates were successful, or 400 if one
 * or more updates failed. In the latter case, more information can be
 * found in the log file (if logging is enabled).
 */

require 'vendor/autoload.php';

use Update\Updater\NoIPUpdater;
use Config\Config;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//check if a secret is set
if(!isset(Config::$secret)){
    send_response('You have to set a secret. Please review your configuration.', 501);
    return;
}

//check if secrets match
$secret = isset($_REQUEST['secret']) ? $_REQUEST['secret'] : '' ;

if($secret !== Config::$secret){
    send_response('Secret mismatch.', 401);
    return;
}

//check if rest of the config is sane
$err = false;

$err = $err || !isset(Config::$enable_logging);
$err = $err || !isset(Config::$log_dir);
$err = $err || !isset(Config::$hosts) || !is_array(Config::$hosts);

if ($err){
    send_response('The configuration contains errors.', 501);
    return;
}

//check if hosts are properly set
foreach (Config::$hosts as $host){

    if(!is_array($host)){
        $err = true;
        break;
    }

    if(!isset($host['provider']) || !isset($host['host'])
        || !isset($host['user']) || !isset($host['password'])){
        $err = true;
        break;
    }
}

if ($err){
    send_response('The host configuration contains errors.', 501);
    return;
}

// check if log dir exists
if(Config::$enable_logging){
    if(!is_dir(Config::$log_dir) || !is_writeable(Config::$log_dir)){
        send_response('Specified log directory is not a directory or not writeable.', 501);
        return;
    }
}

// init logging
$log = new Logger('MDDNSUpdater');

if(Config::$enable_logging){
    $logpath = Config::$log_dir.'/MDDNSUpdater_'.date('d-m-y_H-i-s').'.log';
    $log->pushHandler(new StreamHandler($logpath, Logger::INFO));
}

//check for ip param
$ip = isset($_REQUEST['ip']) ? $_REQUEST['ip'] : '' ;

if(empty($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false){
    send_response('Please pass a valid IP address as a parameter.', 400);
    return;
}


// start the update for each host
$log->addInfo('Starting to update '.count(Config::$hosts).' hosts');

$responses = array();

foreach (Config::$hosts as $host){

    $log->addInfo('Updating '.$host['host']);

    $updater = null;

    // determine which updater to use
    // insert new updaters here
    switch($host['provider']){
        case 'NoIP':
            $updater = new NoIPUpdater();
            break;
        default:
            break;
    }

    if(is_null($updater)){
        $log->addInfo('No updater for provider '.$host['provider'].' found');
        continue;
    }

    $updater->setUser($host['user'], $host['password']);

    $response = $updater->updateHost($host['host'], $ip);

    if($response->isError()){
        $log->addInfo('Error while updating '.$host['host'].':'.$response->getErrorMessage());
    }else{
        $log->addInfo('Successfully updated '.$host['host']);
    }

    $responses[] = $response;

    $log->addInfo('Finished updating '.$host['host']);

}

$log->addInfo('Finished updating all hosts');

$updateSuccess = array_reduce($responses, function ($carry, $resp){
    return $carry && !$resp->isError();
}, true);

if($updateSuccess){
    send_response('All hosts updated successfully.', 200);
}else{
    send_response('One or more errors encountered while updating hosts. See log for more information.', 400);
}

/**
 * Sends a message with the HTTP status code respCode to the client
 *
 * @param $message Message
 * @param int $respCode HTTP status code
 */
function send_response($message, $respCode = null){
    global $log;

    if(!is_null($respCode)){
        http_response_code($respCode);
    }

    if($log){
        $log->addInfo('Message sent: '.$message);
    }

    echo $message;
}