<?php

namespace Update\Updater;
use Update\Response;

/**
 * Interface for all updaters. A separate updater
 * is used for each DDNS provider and should implement
 * their specified protocol.
 *
 * @package Update\Updater
 */
interface Updater {

    /**
     * Sets the name and the password which should
     * be used to login. The updater does not have
     * to use these values if they are not needed
     *
     * @param $name
     * @param $password
     */
    public function setUser($name, $password);

    /**
     * Performs the update of the entry for
     * the host to the new ip
     *
     * @param $host Hostname
     * @param $ip IP the host should be set to
     * @return Response Response indicating success or failure
     */
    public function updateHost($host, $ip);

}

