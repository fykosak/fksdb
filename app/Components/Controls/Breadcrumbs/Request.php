<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use FKSDB\Models\UI\PageTitle;
use Nette\Application\Request as AppRequest;

/**
 * POD to be stored in the session
 */
class Request {

    public ?int $user;
    public AppRequest $request;
    public PageTitle $title;
    /** @var string|null backLing ID */
    public ?string $parent;
    public string $pathKey;

    public function __construct(?int $user, AppRequest $request, PageTitle $title, ?string $parent, string $pathKey) {
        $this->user = $user;
        $this->request = $request;
        $this->title = $title;
        $this->parent = $parent;
        $this->pathKey = $pathKey;
    }
}
