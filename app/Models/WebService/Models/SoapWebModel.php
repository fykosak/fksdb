<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

interface SoapWebModel
{
    public function getSOAPResponse(\stdClass $args): \SoapVar;
}
