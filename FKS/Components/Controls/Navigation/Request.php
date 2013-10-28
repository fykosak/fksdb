<?php

namespace FKS\Components\Controls\Navigation;

use Nette\Application\Request as AppRequest;

/**
 *
 * POD to be stored in the session
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Request {

    /**
     * @var null|int
     */
    public $user;

    /**
     * @var AppRequest
     */
    public $request;

    /**
     * @var string
     */
    public $title;
    /*
     * @var string backling ID
     */
    public $parent;

    /**
     * @var string 
     */
    public $pathKey;

    function __construct($user, AppRequest $request, $title, $parent, $pathKey) {
        $this->user = $user;
        $this->request = $request;
        $this->title = $title;
        $this->parent = $parent;
        $this->pathKey = $pathKey;
    }

}
