<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Exceptions\ModelException;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @property ModelStoredQuery|null $model
 */
class StoredQueryFormComponent extends AbstractEntityFormComponent
{

    private const CONT_SQL = 'sql';
    private const CONT_PARAMS = 'params';
    private const CONT_MAIN = 'main';
    private ServiceStoredQuery $serviceStoredQuery;
    private ServiceStoredQueryTag $serviceStoredQueryTag;
    private ServiceStoredQueryParameter $serviceStoredQueryParameter;
    private StoredQueryFactory $storedQueryFactory;
    private SingleReflectionFormFactory $reflectionFormFactory;

    final public function injectPrimary(
        ServiceStoredQuery $serviceStoredQuery,
        ServiceStoredQueryTag $serviceStoredQueryTag,
        ServiceStoredQueryParameter $serviceStoredQueryParameter,
        StoredQueryFactory $storedQueryFactory,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    /**
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = FormUtils::emptyStrToNull($form->getValues(), true);
        $connection = $this->serviceStoredQuery->explorer->getConnection();
        $connection->beginTransaction();

        $data = array_merge($values[self::CONT_SQL], $values[self::CONT_MAIN]);

        if (isset($this->model)) {
            $model = $this->model;
            $this->serviceStoredQuery->updateModel($model, $data);
        } else {
            $model = $this->serviceStoredQuery->createNewModel($data);
        }

        $this->saveTags($values[self::CONT_MAIN]['tags'], $model);
        $this->saveParameters($values[self::CONT_PARAMS], $model);

        $connection->commit();
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Query has been edited') : _('Query has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $group = $form->addGroup(_('SQL'));
        $form->addComponent($this->createConsole($group), self::CONT_SQL);

        $group = $form->addGroup(_('Parameters'));
        $form->addComponent($this->createParametersMetadata($group), self::CONT_PARAMS);

        $group = $form->addGroup(_('Metadata'));
        $form->addComponent($this->createMetadata($group), self::CONT_MAIN);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Execute'))
            ->setValidationScope(null);
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-success']);
        $submit->onClick[] = function (SubmitButton $button) {
            $this->handleComposeExecute($button->getForm());
        };
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createMetadata(?ControlGroup $group = null): ModelContainer
    {
        $container = $this->reflectionFormFactory->createContainer(
            'stored_query',
            ['name', 'qid', 'tags', 'description']
        );
        $container->setCurrentGroup($group);
        return $container;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createConsole(?ControlGroup $group = null): ModelContainer
    {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);
        $control = $this->reflectionFormFactory->createField('stored_query', 'sql');
        $container->addComponent($control, 'sql');
        return $container;
    }

    private function saveTags(array $tags, ModelStoredQuery $query): void
    {
        $this->serviceStoredQueryTag->getTable()->where([
            'query_id' => $query->query_id,
        ])->delete();
        foreach ($tags as $tagTypeId) {
            $this->serviceStoredQueryTag->createNewModel([
                'query_id' => $query->query_id,
                'tag_type_id' => $tagTypeId,
            ]);
        }
    }

    private function createParametersMetadata(?ControlGroup $group = null): Replicator
    {
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

        $submit->setValidationScope(null)
            ->addCreateOnClick();

        return $replicator;
    }

    private function buildParameterMetadata(Container $container, ControlGroup $group): void
    {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Parameter name'))
            ->addRule(Form::FILLED, _('Parameter name is required.'))
            ->addRule(Form::MAX_LENGTH, _('Parameter name is too long.'), 16)
            ->addRule(
                Form::PATTERN,
                _(
                    'The name of the parameter can only contain lowercase 
                letters of the english alphabet, numbers, and an underscore.'
                ),
                '[a-z][a-z0-9_]*'
            );

        $container->addText('description', _('Description'));

        $container->addSelect('type', _('Data type'))
            ->setItems([
                ModelStoredQueryParameter::TYPE_INT => 'integer',
                ModelStoredQueryParameter::TYPE_STRING => 'string',
                ModelStoredQueryParameter::TYPE_BOOL => 'bool',
            ]);

        $container->addText('default', _('Default value'));
    }

    private function saveParameters(array $parameters, ModelStoredQuery $query): void
    {
        $this->serviceStoredQueryParameter->getTable()
            ->where(['query_id' => $query->query_id])->delete();

        foreach ($parameters as $paramMetaData) {
            $data = (array)$paramMetaData;
            $data['query_id'] = $query->query_id;
            $data = array_merge(
                $data,
                ModelStoredQueryParameter::setInferDefaultValue($data['type'], $paramMetaData['default'])
            );
            $this->serviceStoredQueryParameter->createNewModel($data);
        }
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $values = [];
            $values[self::CONT_SQL] = $this->model;
            $values[self::CONT_MAIN] = $this->model->toArray();
            $values[self::CONT_MAIN]['tags'] = $this->model->getTags()->fetchPairs('tag_type_id', 'tag_type_id');
            $values[self::CONT_PARAMS] = [];
            foreach ($this->model->getParameters() as $parameter) {
                $paramData = $parameter->toArray();
                $paramData['default'] = $parameter->getDefaultValue();
                $values[self::CONT_PARAMS][] = $paramData;
            }
            $this->getForm()->setDefaults($values);
        }
    }

    protected function createComponentQueryResultsComponent(): ResultsComponent
    {
        $grid = new ResultsComponent($this->getContext());
        $grid->setShowParametrizeForm(false);
        return $grid;
    }

    private function handleComposeExecute(Form $form): void
    {
        $data = $form->getValues(true);
        $parameters = [];
        foreach ($data[self::CONT_PARAMS] as $paramMetaData) {
            $parameters[] = new StoredQueryParameter(
                $paramMetaData['name'],
                $paramMetaData['default'],
                ModelStoredQueryParameter::staticGetPDOType($paramMetaData['type'])
            );
        }
        $query = $this->storedQueryFactory->createQueryFromSQL(
            $this->getPresenter(),
            $data[self::CONT_SQL]['sql'],
            $parameters
        );
        /** @var ResultsComponent $control */
        $control = $this->getComponent('queryResultsComponent');
        $control->setStoredQuery($query);
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.storedQuery.latte';
    }
}
