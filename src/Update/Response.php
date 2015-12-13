<?php

namespace Update;
use Update\Actions\Action;

/**
 * Class Response
 *
 * A response is a result of a host update performed by an Updater.
 *
 * @package Update
 */
class Response{

    /**
     * Indiciates whether the update was successful or not
     *
     * @var bool
     */
    private $error;

    /**
     * If the update failed, this field contains the error message
     *
     * @var string
     */
    private $errormessage;

    /**
     * Action attached to the response
     *
     * @var Action
     */
    private $action;

    /**
     * Response constructor.
     *
     * @param bool $error
     * @param string $errormessage
     * @param null $action
     */
    public function __construct($error = false, $errormessage = '', $action = null){
        $this->error = $error;
        $this->errormessage = $errormessage;
        $this->action = $action;
    }

    /**
     * Returns if there has been an error
     *
     * @return bool
     */
    public function isError(){
        return $this->error;
    }

    /**
     * Returns the error message (if any)
     *
     * @return string
     */
    public function getErrorMessage(){
        return $this->errormessage;
    }

    /**
     * Returns if the response has an action attached to it
     *
     * @return bool
     */
    public function actionRequired(){
        return !is_null($this->action);
    }

    /**
     * Getter for the action
     *
     * @return null|Action
     */
    public function getAction(){
        return $this->action;
    }

}