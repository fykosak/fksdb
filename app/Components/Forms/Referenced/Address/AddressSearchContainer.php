<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced\Address;

use FKSDB\Components\Forms\Referenced\SearchContainer;
use Nette\Forms\Controls\BaseControl;

class AddressSearchContainer extends SearchContainer
{

    protected function createSearchControl(): ?BaseControl
    {
        return null;
    }

    protected function getSearchCallback(): callable
    {
        return fn() => null;
    }

    protected function getTermToValuesCallback(): callable
    {
        return fn() => [];
    }
}
