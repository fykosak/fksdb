<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterType;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Models\StoredQuery\TagModel;
use FKSDB\Models\ORM\Services\StoredQuery\ParameterService;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use FKSDB\Models\ORM\Services\StoredQuery\TagService;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\OrganizerModule\BasePresenter;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\ControlGroup;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-extends ModelForm<QueryModel,array{sql:array<mixed>,params:array<mixed>,main:array<mixed>}>
 * @method BasePresenter getPresenter()
 */
class StoredQueryFormComponent extends ModelForm
{
    private const CONT_SQL = 'sql';
    private const CONT_PARAMS = 'params';
    private const CONT_MAIN = 'main';
    private QueryService $storedQueryService;
    private TagService $storedQueryTagService;
    private ParameterService $storedQueryParameterService;
    private StoredQueryFactory $storedQueryFactory;

    final public function injectPrimary(
        QueryService $storedQueryService,
        TagService $storedQueryTagService,
        ParameterService $storedQueryParameterService,
        StoredQueryFactory $storedQueryFactory
    ): void {
        $this->storedQueryService = $storedQueryService;
        $this->storedQueryTagService = $storedQueryTagService;
        $this->storedQueryParameterService = $storedQueryParameterService;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    protected function configureForm(Form $form): void
    {
        $group = $form->addGroup(_('SQL'));
        $form->addComponent($this->createConsole($group), self::CONT_SQL);

        $group = $form->addGroup(_('Parameters'));
        /** @phpstan-ignore-next-line */
        $form->addComponent($this->createParametersMetadata($group), self::CONT_PARAMS);

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
     * @throws ForbiddenRequestException
     */
    private function createMetadata(?ControlGroup $group = null): ModelContainer
    {
        $container = new ModelContainer($this->container, 'stored_query');

        $container->addField('name', ['required' => true]);
        $container->addField('qid', ['required' => false]);
        $container->addField('tags', ['required' => false]);
        $container->addField('description', ['required' => false]);
        $container->setCurrentGroup($group);
        return $container;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     */
    private function createConsole(?ControlGroup $group = null): ContainerWithOptions
    {
        $container = new ModelContainer($this->container, 'stored_query');
        $container->setCurrentGroup($group);
        $container->addField('sql');
        return $container;
    }

    /**
     * @phpstan-param int[] $tags
     */
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
    /** @phpstan-ignore-next-line */
    private function createParametersMetadata(?ControlGroup $group = null): Replicator
    {
        /** @phpstan-ignore-next-line */
        $replicator = new Replicator(function (ContainerWithOptions $replContainer) use ($group): void {
            $this->buildParameterMetadata($replContainer, $group);

            $submit = $replContainer->addSubmit('remove', _('Remove parameter'));
            $submit->getControlPrototype()->addAttributes(['class' => 'btn-outline-danger']);
            $submit->addRemoveOnClick();// @phpstan-ignore-line
        }, $this->container, 0, true);
        /** @phpstan-ignore-next-line */
        $replicator->setCurrentGroup($group);
        /** @phpstan-ignore-next-line */
        $submit = $replicator->addSubmit('addParam', _('Add parameter'));
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-outline-success']);
        $submit->setValidationScope(null)->addCreateOnClick();

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

    /**
     * @phpstan-param array<array<string,mixed>> $parameters
     */
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

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    private function handleComposeExecute(Form $form): void
    {
        /** @phpstan-var array{params:array{name:string,default:mixed,type:string},sql:array{sql:string}} $data */
        $data = $form->getValues('array');
        $parameters = [];
        foreach ($data[self::CONT_PARAMS] as $paramMetaData) {
            $parameters[] = new StoredQueryParameter(
                $paramMetaData['name'],
                $paramMetaData['default'],
                ParameterType::from($paramMetaData['type'])
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

    protected function innerSuccess(array $values, Form $form): QueryModel
    {
        $data = array_merge($values[self::CONT_SQL], $values[self::CONT_MAIN]);
        /** @var QueryModel $model */
        $model = $this->storedQueryService->storeModel($data);

        $this->saveTags($values[self::CONT_MAIN]['tags'], $model);
        $this->saveParameters($values[self::CONT_PARAMS], $model);
        return $model;
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model) ? _('Query has been edited') : _('Query has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list');
    }
}
