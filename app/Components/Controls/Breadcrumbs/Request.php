<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use FKSDB\UI\PageTitle;
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
     * @var PageTitle
     */
    public $title;
    /**
     * @var string backling ID
     */
    public $parent;

    /**
     * @var string
     */
    public $pathKey;

    /**
     * Request constructor.
     * @param int|null $user
     * @param AppRequest $request
     * @param PageTitle $title
     * @param string $parent
     * @param string $pathKey
     */
    function __construct($user, AppRequest $request, PageTitle $title, string $parent, string $pathKey) {
        $this->user = $user;
        $this->request = $request;
        $this->title = $title;
        $this->parent = $parent;
        $this->pathKey = $pathKey;
    }
}
