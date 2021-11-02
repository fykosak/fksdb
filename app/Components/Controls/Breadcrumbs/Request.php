<?php

namespace FKSDB\Components\Controls\Breadcrumbs;

use Fykosak\Utils\UI\Title;
use Nette\Application\Request as AppRequest;

/**
 * POD to be stored in the session
 */
class Request
{

    public ?int $user;
    public AppRequest $request;
    public Title $title;
    /** @var string|null backLing ID */
    public ?string $parent;
    public string $pathKey;

    public function __construct(?int $user, AppRequest $request, Title $title, ?string $parent, string $pathKey)
    {
        $this->user = $user;
        $this->request = $request;
        $this->title = $title;
        $this->parent = $parent;
        $this->pathKey = $pathKey;
    }
}
