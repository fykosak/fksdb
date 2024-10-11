<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\SearchContainer;

use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonSelectBox;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * @phpstan-extends SearchContainer<PersonModel>
 */
class PersonSearchContainer extends SearchContainer
{
    public const SEARCH_EMAIL = 'email';
    public const SEARCH_ID = 'id';
    public const SEARCH_NONE = 'none';

    private string $searchType;

    protected PersonService $personService;

    public function __construct(Container $container, string $searchType)
    {
        parent::__construct($container);
        $this->searchType = $searchType;
    }

    final public function injectPrimary(
        PersonService $personService
    ): void {
        $this->personService = $personService;
    }

    protected function createSearchControl(): ?BaseControl
    {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('Email'));
                $control->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, _('Invalid email.'));
                $control->setOption(
                    'description',
                    _('First of all try to find the person in our database using email address')
                );
                $control->setHtmlAttribute('placeholder', 'your-email@example.com');
                $control->setHtmlAttribute('autocomplete', 'email');
                return $control;
            case self::SEARCH_ID:
                return new PersonSelectBox(true, new PersonProvider($this->container), _('Person'));
            case self::SEARCH_NONE:
                return null;
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    /**
     * @phpstan-return callable(string|null):(PersonModel|null)
     */
    protected function getSearchCallback(): callable
    {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                return fn(?string $term): ?PersonModel => $this->personService->findByEmail($term);
            case self::SEARCH_ID:
                return fn(?string $term): ?PersonModel => $this->personService->findByPrimary($term);
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    /**
     * @phpstan-return callable(string|null):array<string,array<string,string|null>>)
     */
    protected function getTermToValuesCallback(): callable
    {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                return fn(?string $term): array => ['person_info' => ['email' => $term]];
            case self::SEARCH_ID:
                return fn(): array => [];
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }
}
