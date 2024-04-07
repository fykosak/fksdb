<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\Exceptions\GoneException;

interface SoapWebModel
{
    /**
     * @throws GoneException
     */
    public function getResponse(\stdClass $args): \SoapVar;
}