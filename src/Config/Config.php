<?php

namespace Config;

/**
 * Main configuration.
 *
 * Class Config
 * @package Config
 */
class Config{

    /**
     * The updater has to be accessible on a webserver, which could be publicly accessable.
     * To avoid having random strangers updating the IP of the hosts, all client requests
     * have to include this secret.
     *
     * @var string Secret key (should be a sufficient long random string)
     */
    public static $secret = 'INSERT_YOUR_SECRET_HERE';

    /**
     * Indiciates whether logging should be enabled or not.
     *
     * @var bool
     */
    public static $enable_logging = true;

    /**
     * Directory for all log files.
     * This directory must exist and has to be writeable by the webserver
     * if logging is enabled.
     *
     * @var string Path of the log directory
     */
    public static $log_dir = 'log';

    /**
     * The host configuration. Each entry is a associative array
     * with the string fields 'provider', 'host', 'user' and 'password'.
     * Provider is the name of the updater as specified in the main file.
     * The rest of the fields are believed to be self explanatory.
     *
     * @var array
     */
    public static $hosts = array(
        array(
            'provider' => 'NoIP',
            'host' => 'example.org',
            'user' => 'test@example.org',
            'password' => 'password'
        )
    );
}