<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Containers\IReferencedSetter;
use FKS\Components\Forms\Containers\ReferencedContainer;
use FKS\Components\Forms\Controls\ReferencedId;
use ModelPerson;
use ModelPostContact;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Object;
use ORM\IModel;
use Persons\ReferencedPersonHandlerFactory;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReferencedPersonFactory extends Object implements IReferencedSetter {

    const SEARCH_EMAIL = 'email';
    const SEARCH_ID = 'id';
    const SEARCH_NONE = 'none';

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ReferencedPersonHandlerFactory
     */
    private $referencedPersonHandlerFactory;

    function __construct(ServicePerson $servicePerson, PersonFactory $personFactory, ReferencedPersonHandlerFactory $referencedPersonHandlerFactory) {
        $this->servicePerson = $servicePerson;
        $this->personFactory = $personFactory;
        $this->referencedPersonHandlerFactory = $referencedPersonHandlerFactory;
    }

    public function createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, $fillingMode, $resolution) {

        $handler = $this->referencedPersonHandlerFactory->create($acYear, $resolution);

        $hiddenField = new ReferencedId($this->servicePerson, $handler, $this);

        $container = new ReferencedContainer($hiddenField);
        if ($searchType == self::SEARCH_NONE) {
            $container->setSearch();
        } else {
            $container->setSearch($this->createSearchControl($searchType), $this->createSearchCallback($searchType), $this->createTermToValuesCallback($searchType));
        }

        $container->setAllowClear($allowClear);
        $container->setFillingMode($fillingMode);
        $container->setMetadata($acYear);

        foreach ($fieldsDefinition as $sub => $fields) {
            $subcontainer = new ContainerWithOptions();
            foreach ($fields as $fieldName => $metadata) {
                if (is_scalar($metadata)) {
                    $metadata = array(
                        'required' => $metadata,
                    );
                }
                $control = $this->personFactory->createField($sub, $fieldName, $acYear, $hiddenField, $metadata);

                $subcontainer->addComponent($control, $fieldName);
            }
            $container->addComponent($subcontainer, $sub);
        }

        return array(
            $hiddenField,
            $container,
        );
    }

    public function setModel(ReferencedContainer $container, IModel $model) {
        $fillingMode = $container->getFillingMode();
        $acYear = $container->getMetadata();

        $container->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model->getFullname());

        foreach ($container->getComponents() as $sub => $subcontainer) {
            if (!$subcontainer instanceof Container) {
                continue;
            }

            foreach ($subcontainer->getComponents() as $fieldName => $control) {
                $value = $this->getPersonValue($model, $sub, $fieldName, $acYear);
                if ($value) {
                    if ($fillingMode == ReferencedContainer::FILLED_HIDDEN) {
                        $container[$sub]->removeComponent($control);
                    } else if ($fillingMode == ReferencedContainer::FILLED_DISABLED) {
                        $control->setDisabled();
                        $control->setValue($value);
                    } else if ($fillingMode == ReferencedContainer::FILLED_MODIFIABLE) {
                        $control->setValue($value);
                    }
                }
            }
        }
    }

    private function createSearchControl($searchType) {
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                $control = new TextInput(_('E-mail'));
                $control->addCondition(Form::FILLED)
                        ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
                $control->setOption('description', _('Nejprve zkuste najít osobu v naší databázi podle e-mailu.'));
                break;
            case self::SEARCH_ID:
                $control = $this->personFactory->createPersonSelect(true, _('Jméno'), $this->personProvider);
        }
        return $control;
    }

    private function createSearchCallback($searchType) {
        $service = $this->servicePerson;
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return function($term) use($service) {
                            return $service->findByEmail($term);
                        };

                break;
            case self::SEARCH_ID:
                return function($term) use($service) {
                            return $service->findByPrimary($term);
                        };
        }
    }

    private function createTermToValuesCallback($searchType) {
        switch ($searchType) {
            case self::SEARCH_EMAIL:
                return function($term) {
                            return array('person_info' => array('email' => $term));
                        };
                break;
            case self::SEARCH_ID:
                return function($term) {
                            return array();
                        };
        }
    }

    private function getPersonValue(ModelPerson $person, $sub, $field, $acYear) {
        switch ($sub) {
            case 'person':
                return $person[$field];
            case 'person_info':
                return ($info = $person->getInfo()) ? $info[$field] : null;
            case 'person_history':
                return ($history = $person->getHistory($acYear)) ? $history[$field] : null;
            case 'post_contact':
                if ($field == 'type') {
                    return ModelPostContact::TYPE_PERMANENT; //TODO distinquish delivery and permanent address
                } else if ($field == 'address') {
                    return $person->getPermanentAddress(); //TODO distinquish delivery and permanent address
                }
        }
    }

}

