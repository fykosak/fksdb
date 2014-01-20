<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Containers\ContainerWithOptions;
use FKS\Components\Forms\Containers\IReferencedSetter;
use FKS\Components\Forms\Containers\IWriteonly;
use FKS\Components\Forms\Containers\ReferencedContainer;
use FKS\Components\Forms\Controls\ReferencedId;
use ModelPerson;
use ModelPostContact;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Object;
use ORM\IModel;
use Persons\IModifialibityResolver;
use Persons\IVisibilityResolver;
use Persons\ReferencedPersonHandler;
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

    /**
     * 
     * @param type $fieldsDefinition
     * @param type $acYear
     * @param type $searchType
     * @param type $allowClear
     * @param IModifialibityResolver $modifiabilityResolver is person's filled field modifiable?
     * @param IVisibilityResolver $visibilityResolver is person's writeonly field visible? (i.e. not writeonly then)
     * @return array
     */
    public function createReferencedPerson($fieldsDefinition, $acYear, $searchType, $allowClear, IModifialibityResolver $modifiabilityResolver, IVisibilityResolver $visibilityResolver) {

        $handler = $this->referencedPersonHandlerFactory->create($acYear);

        $hiddenField = new ReferencedId($this->servicePerson, $handler, $this);

        $container = new ReferencedContainer($hiddenField);
        if ($searchType == self::SEARCH_NONE) {
            $container->setSearch();
        } else {
            $container->setSearch($this->createSearchControl($searchType), $this->createSearchCallback($searchType), $this->createTermToValuesCallback($searchType));
        }

        $container->setAllowClear($allowClear);
        $container->setOption('acYear', $acYear);
        $container->setOption('modifiabilityResolver', $modifiabilityResolver);
        $container->setOption('visibilityResolver', $visibilityResolver);

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

    public function setModel(ReferencedContainer $container, IModel $model = null, $mode = self::MODE_NORMAL) {
        $acYear = $container->getOption('acYear');
        $modifiable = $model ? $container->getOption('modifiabilityResolver')->isModifiable($model) : true;
        $resolution = $model ? $container->getOption('modifiabilityResolver')->getResolutionMode($model) : ReferencedPersonHandler::RESOLUTION_OVERWRITE;
        $visible = $model ? $container->getOption('visibilityResolver')->isVisible($model) : true;
        $submittedBySearch = $container->isSearchSubmitted();
        $force = ($mode == self::MODE_FORCE);
        if ($mode == self::MODE_ROLLBACK) {
            $model = null;
        }

        $container->getReferencedId()->getHandler()->setResolution($resolution);
        $container->getComponent(ReferencedContainer::CONTROL_COMPACT)->setValue($model ? $model->getFullname() : null);

        foreach ($container->getComponents() as $sub => $subcontainer) {
            if (!$subcontainer instanceof Container) {
                continue;
            }

            foreach ($subcontainer->getComponents() as $fieldName => $component) {
                $value = $this->getPersonValue($model, $sub, $fieldName, $acYear);

                $controlModifiable = $value ? $modifiable : true;
                $controlVisible = ($component instanceof IWriteonly) ? $visible : true;

                if (!$controlVisible && !$controlModifiable) {
                    $container[$sub]->removeComponent($component);
                } else if (!$controlVisible && $controlModifiable) {
                    $component->setWriteonly(true);
                } else if ($controlVisible && !$controlModifiable) {
                    $component->setDisabled();
                } else if ($controlVisible && $controlModifiable) {
                    if ($component instanceof IWriteonly) {
                        $component->setWriteonly(false);
                    }
                }
                if ($mode == self::MODE_ROLLBACK) {
                    $component->setDisabled(false);
                    if ($component instanceof IWriteonly) {
                        $component->setWriteonly(false);
                    }
                } else {
                    if ($submittedBySearch || $force) {
                        $component->setValue($value);
                    } else {
                        $component->setDefaultValue($value);
                    }
                    if ($value && $resolution == ReferencedPersonHandler::RESOLUTION_EXCEPTION) {
                        $component->setDisabled(); // could not store different value anyway
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

    private function getPersonValue(ModelPerson $person = null, $sub, $field, $acYear) {
        if (!$person) {
            return null;
        }
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

