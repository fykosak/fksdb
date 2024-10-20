<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Referenced\Address;

use FKSDB\Components\Forms\Containers\SearchContainer\SearchContainer;
use FKSDB\Models\ORM\Models\AddressModel;
use Nette\Forms\Controls\BaseControl;

/**
 * @phpstan-extends SearchContainer<AddressModel>
 */
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
