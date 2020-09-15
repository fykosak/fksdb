<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\DBReflection\OmittedControlException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    private ServiceStoredQueryTagType $serviceStoredQueryTagType;

    private SingleReflectionFormFactory $reflectionFormFactory;

    /**
     * StoredQueryFactory constructor.
     * @param ServiceStoredQueryTagType $serviceStoredQueryTagType
     * @param SingleReflectionFormFactory $reflectionFormFactory
     */
    public function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType, SingleReflectionFormFactory $reflectionFormFactory) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    /**
     * @param ControlGroup|null $group
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws OmittedControlException
     * @throws BadTypeException
     */
    public function createConsole(?ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);
        $control = $this->reflectionFormFactory->createField('stored_query', 'sql');
        $container->addComponent($control, 'sql');
        return $container;
    }

    /**
     * @param ControlGroup|null $group
     * @return ModelContainer
     * @throws AbstractColumnException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createMetadata(?ControlGroup $group = null): ModelContainer {
        $container = $this->reflectionFormFactory->createContainer('stored_query', ['name', 'qid', 'tags', 'description']);
        $container->setCurrentGroup($group);

        $control = $this->reflectionFormFactory->createField('stored_query', 'php_post_proc')->setDisabled(true);
        $container->addComponent($control, 'php_post_proc');
        return $container;
    }

    public function createParametersMetadata(?ControlGroup $group = null): Replicator {
        $replicator = new Replicator(function (Container $replContainer) use ($group) {
            $this->buildParameterMetadata($replContainer, $group);

            $submit = $replContainer->addSubmit('remove', _('Remove parameter'));
            $submit->getControlPrototype()->addAttributes(['class' => 'btn-danger btn-sm']);
            $submit->addRemoveOnClick();
        }, 0, true);
        $replicator->containerClass = ModelContainer::class;
        $replicator->setCurrentGroup($group);
        $submit = $replicator->addSubmit('addParam', _('Add parameter'));
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-sm btn-success']);

        $submit->setValidationScope(false)
            ->addCreateOnClick();

        return $replicator;
    }

    private function buildParameterMetadata(Container $container, ControlGroup $group): void {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Parameter name'))
            ->addRule(Form::FILLED, _('Parameter name is required.'))
            ->addRule(Form::MAX_LENGTH, _('Parameter name is too long.'), 16)
            ->addRule(Form::PATTERN, _('Název parametru může být jen z malých písmen anglické abecedy, číslic nebo podtržítka.'), '[a-z][a-z0-9_]*');

        $container->addText('description', _('Description'));

        $container->addSelect('type', _('Data type'))
            ->setItems([
                ModelStoredQueryParameter::TYPE_INT => 'integer',
                ModelStoredQueryParameter::TYPE_STRING => 'string',
                ModelStoredQueryParameter::TYPE_BOOL => 'bool',
            ]);

        $container->addText('default', _('Default value'));
    }

    /**
     * @param ModelStoredQueryParameter[] $queryParameters
     * @param ControlGroup|null $group
     * @return ModelContainer
     * TODO
     */
    public function createParametersValues(array $queryParameters, ?ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        foreach ($queryParameters as $parameter) {
            $name = $parameter->name;
            $subContainer = new ModelContainer();
            $container->addComponent($subContainer, $name);
            // $subcontainer = $container->addContainer($name);

            switch ($parameter->type) {
                case ModelStoredQueryParameter::TYPE_INT:
                case ModelStoredQueryParameter::TYPE_STRING:
                    $valueElement = $subContainer->addText('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                        $valueElement->addRule(Form::INTEGER, _('Parameter %label is numeric.'));
                    }

                    $valueElement->setDefaultValue($parameter->getDefaultValue());
                    break;
                case ModelStoredQueryParameter::TYPE_BOOL:
                    $valueElement = $subContainer->addCheckbox('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    $valueElement->setDefaultValue((bool)$parameter->getDefaultValue());
                    break;
            }
        }

        return $container;
    }
}
