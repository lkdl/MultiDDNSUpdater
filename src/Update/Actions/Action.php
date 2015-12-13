<?php

namespace Update\Actions;

/**
 * Interface Action
 *
 * Actions can be attached to responses and should be executed
 * when the update has been performed. Possible actions could be
 * notifying the user if something went wrong or  rescheduling
 * the update if it failed.
 *
 * Attention: This is a feature which not implemented yet.
 *
 * @package Update\Actions
 */
interface Action{

    public function perform();

}