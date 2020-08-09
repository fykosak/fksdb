<?php

namespace FKSDB\Components\Forms\Containers\SearchContainer;

use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\PersonFactory;
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
    const SEARCH_EMAIL = 'email';
    const SEARCH_ID = 'id';
    const SEARCH_NONE = 'none';

    protected PersonFactory $personFactory;

    private string $searchType;

    protected PersonProvider $personProvider;

    protected ServicePerson $servicePerson;

    /**
     * SearchContainer constructor.
     * @param Container $container
     * @param string $searchType
     */
    public function __construct(Container $container, string $searchType) {
        parent::__construct($container);
        $this->searchType = $searchType;
    }

    public function injectPrimary(PersonFactory $personFactory, ServicePerson $servicePerson, PersonProvider $provider): void {
        $this->personFactory = $personFactory;
        $this->servicePerson = $servicePerson;
        $this->personProvider = $provider;
    }

    /**
     * @return BaseControl|null
     */
    protected function createSearchControl() {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('E-mail'));
                $control->addCondition(Form::FILLED)
                    ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                $control->setOption('description', _('Nejprve zkuste najít osobu v naší databázi podle e-mailu.'));
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
                return function ($term) {
                    return $this->servicePerson->findByEmail($term);
                };
            case self::SEARCH_ID:
                return function ($term) {
                    return $this->servicePerson->findByPrimary($term);
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }

    protected function getTermToValuesCallback(): callable {
        switch ($this->searchType) {
            case self::SEARCH_EMAIL:
                return function ($term) {
                    return ['person_info' => ['email' => $term]];
                };
                break;
            case self::SEARCH_ID:
                return function () {
                    return [];
                };
            default:
                throw new InvalidArgumentException(_('Unknown search type'));
        }
    }
}
