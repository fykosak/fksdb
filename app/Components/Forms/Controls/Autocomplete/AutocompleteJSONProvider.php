<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use Nette\Application\IPresenter;

interface AutocompleteJSONProvider extends IPresenter
{

    public function handleAutocomplete(string $acName): void;
}
