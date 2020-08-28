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

    /** @var null|int */
    public $user;

    public AppRequest $request;

    public PageTitle $title;

    /** @var string backLing ID */
    public $parent;

    /** @var string */
    public $pathKey;

    /**
     * Request constructor.
     * @param int|null $user
     * @param AppRequest $request
     * @param PageTitle $title
     * @param string $parent
     * @param string $pathKey
     */
    public function __construct($user, AppRequest $request, PageTitle $title, $parent, $pathKey) {
        $this->user = $user;
        $this->request = $request;
        $this->title = $title;
        $this->parent = $parent;
        $this->pathKey = $pathKey;
    }
}
