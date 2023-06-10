<?php

declare(strict_types=1);

namespace FKSDB\Models\UI;

use Nette\SmartObject;

class PageStyleContainer
{
    use SmartObject;

    public array $styleIds = [];

    public string $navBarClassName = 'bg-light navbar-light';

    public string $navBrandPath = '/images/logo/gray.svg';
}
