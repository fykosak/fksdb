<?php

namespace FKSDB\Components\Forms\Containers\SearchContainer;

use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Nette\DI\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

/**
 * Class SearchContainer
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonSearchContainer extends SearchContainer {
    public const SEARCH_EMAIL = 'email';
    public const SEARCH_ID = 'id';
    public const SEARCH_NONE = 'none';

    protected PersonFactory $personFactory;

    private string $searchType;

    protected PersonProvider $personProvider;

    protected ServicePerson $servicePerson;

    public function __construct(Container $container, string $searchType) {
        parent::__construct($container);
        $this->searchType = $searchType;
    }

    final public function injectPrimary(PersonFactory $personFactory, ServicePerson $servicePerson, PersonProvider $provider): void {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
        $this->personProvider = $provider;
    }


    protected function createSearchControl(): ?BaseControl {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('E-mail'));
                $control->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, _('Invalid e-mail.'));
                $control->setOption('description', _('First of all try to find the person in our database using e-mail address'));
                $control->setAttribute('placeholder', 'your-email@exmaple.com');
                $control->setAttribute('autocomplete', 'email');
                return $control;
            case self::SEARCH_ID:
                return $this->personFactory->createPersonSelect(true, _('Person'), $this->personProvider);
            case self::SEARCH_NONE:
                return null;
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    protected function getSearchCallback(): callable {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                return function ($term): ?ModelPerson {
                    return $this->servicePerson->findByEmail($term);
                };
            case self::SEARCH_ID:
                return function ($term): ?ModelPerson {
                    return $this->servicePerson->findByPrimary($term);
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    protected function getTermToValuesCallback(): callable {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                return function ($term): array {
                    return ['person_info' => ['email' => $term]];
                };
            case self::SEARCH_ID:
                return function (): array {
                    return [];
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }
}
