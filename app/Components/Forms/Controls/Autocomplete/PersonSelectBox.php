<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * @phpstan-extends AutocompleteSelectBox<PersonProvider>
 */
class PersonSelectBox extends AutocompleteSelectBox
{
    public function __construct(bool $ajax, PersonProvider $provider, ?string $label = null)
    {
        parent::__construct($ajax, $label ?? _('Person'), 'person');
        $this->setDataProvider($provider);
    }
}
