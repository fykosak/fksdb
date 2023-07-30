<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterType;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Models\StoredQuery\TagModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\StoredQuery\ParameterService;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Models\ORM\Services\StoredQuery\TagService;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\OrgModule\BasePresenter;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends EntityFormComponent<QueryModel>
 * @method BasePresenter getPresenter()
 */
class StoredQueryFormComponent extends EntityFormComponent
{
    private const CONT_SQL = 'sql';
    private const CONT_PARAMS = 'params';
    private const CONT_MAIN = 'main';
    private QueryService $storedQueryService;
    private TagService $storedQueryTagService;
    private ParameterService $storedQueryParameterService;
    private StoredQueryFactory $storedQueryFactory;
    private SingleReflectionFormFactory $reflectionFormFactory;

    final public function injectPrimary(
        QueryService $storedQueryService,
        TagService $storedQueryTagService,
        ParameterService $storedQueryParameterService,
        StoredQueryFactory $storedQueryFactory,
        SingleReflectionFormFactory $reflectionFormFactory
    ): void {
        $this->storedQueryService = $storedQueryService;
        $this->storedQueryTagService = $storedQueryTagService;
        $this->storedQueryParameterService = $storedQueryParameterService;
        $this->storedQueryFactory = $storedQueryFactory;
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    /**
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = FormUtils::emptyStrToNull2($form->getValues());
        $connection = $this->storedQueryService->explorer->getConnection();
        $connection->beginTransaction();

        $data = array_merge($values[self::CONT_SQL], $values[self::CONT_MAIN]);

        if (isset($this->model)) {
            $model = $this->model;
            $this->storedQueryService->storeModel($data, $model);
        } else {
            /** @var QueryModel $model */
            $model = $this->storedQueryService->storeModel($data);
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
        $form->addComponent($this->createParametersMetadata($group), self::CONT_PARAMS); // @phpstan-ignore-line

        $group = $form->addGroup(_('Metadata'));
        $form->addComponent($this->createMetadata($group), self::CONT_MAIN);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Execute'))
            ->setValidationScope(null);
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-outline-success']);
        $submit->onClick[] = fn(SubmitButton $button) => $this->handleComposeExecute($button->getForm());
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createMetadata(?ControlGroup $group = null): ModelContainer
    {
        $container = $this->reflectionFormFactory->createContainerWithMetadata(
            'stored_query',
            [
                'name' => ['required' => true],
                'qid' => ['required' => false],
                'tags' => ['required' => false],
                'description' => ['required' => false],
            ]
        );
        $container->setCurrentGroup($group);
        return $container;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    private function createConsole(?ControlGroup $group = null): ContainerWithOptions
    {
        $container = new ContainerWithOptions($this->container);
        $container->setCurrentGroup($group);
        $control = $this->reflectionFormFactory->createField('stored_query', 'sql');
        $container->addComponent($control, 'sql');
        return $container;
    }

    private function saveTags(array $tags, QueryModel $query): void
    {
        /** @var TagModel $tag */
        foreach ($query->getTags() as $tag) {
            $this->storedQueryTagService->disposeModel($tag);
        }
        foreach ($tags as $tagTypeId) {
            $this->storedQueryTagService->storeModel([
                'query_id' => $query->query_id,
                'tag_type_id' => $tagTypeId,
            ]);
        }
    }

    private function createParametersMetadata(?ControlGroup $group = null): Replicator
    {
        $replicator = new Replicator(function (ContainerWithOptions $replContainer) use ($group): void {
            $this->buildParameterMetadata($replContainer, $group);

            $submit = $replContainer->addSubmit('remove', _('Remove parameter'));
            $submit->getControlPrototype()->addAttributes(['class' => 'btn-outline-danger']);
            $submit->addRemoveOnClick();// @phpstan-ignore-line
        }, $this->container, 0, true);
        $replicator->setCurrentGroup($group);
        $submit = $replicator->addSubmit('addParam', _('Add parameter'));
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-outline-success']);

        $submit->setValidationScope(null)->addCreateOnClick(); // @phpstan-ignore-line

        return $replicator;
    }

    private function buildParameterMetadata(ContainerWithOptions $container, ControlGroup $group): void
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
                ParameterType::INT => 'integer',
                ParameterType::STRING => 'string',
                ParameterType::BOOL => 'bool',
            ]);

        $container->addText('default', _('Default value'));
    }

    private function saveParameters(array $parameters, QueryModel $query): void
    {
        /** @var ParameterModel $parameter */
        foreach ($query->getParameters2() as $parameter) {
            $this->storedQueryParameterService->disposeModel($parameter);
        }

        foreach ($parameters as $paramMetaData) {
            $data = (array)$paramMetaData;
            $data['query_id'] = $query->query_id;
            $data = array_merge(
                $data,
                ParameterModel::setInferDefaultValue($data['type'], $paramMetaData['default'])
            );
            $this->storedQueryParameterService->storeModel($data);
        }
    }

    protected function setDefaults(Form $form): void
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
            $form->setDefaults($values);
        }
    }

    protected function createComponentQueryResultsComponent(): ResultsComponent
    {
        $grid = new ResultsComponent($this->getContext());
        $grid->showParametrizeForm = false;
        return $grid;
    }

    private function handleComposeExecute(Form $form): void
    {
        $data = $form->getValues('array');
        $parameters = [];
        foreach ($data[self::CONT_PARAMS] as $paramMetaData) {
            $parameters[] = new StoredQueryParameter(
                $paramMetaData['name'],
                $paramMetaData['default'],
                ParameterType::tryFrom($paramMetaData['type'])
            );
        }
        $query = $this->storedQueryFactory->createQueryFromSQL(
            $this->getPresenter(),
            $data[self::CONT_SQL]['sql'],
            $parameters
        );
        /** @var ResultsComponent $control */
        $control = $this->getComponent('queryResultsComponent');
        $control->storedQuery = $query;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.storedQuery.latte';
    }
}
