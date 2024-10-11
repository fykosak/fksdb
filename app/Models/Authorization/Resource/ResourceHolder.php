<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Resource;

use Nette\Security\Resource;

interface ResourceHolder extends Resource
{
    /**
     * @return string|Resource
     */
    public function getResource();
}
