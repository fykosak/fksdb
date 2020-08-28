<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\StoredQuery\StoredQueryFactory;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\Modules\OrgModule\BasePresenter;
use FKSDB\Modules\OrgModule\StoredQueryPresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\StoredQuery\StoredQueryParameter;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * Class StoredQueryForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @method StoredQueryPresenter getPresenter($throw = true)
 * @property ModelStoredQuery $model
 */
class StoredQueryFormComponent extends EditEntityFormComponent {
    private const CONT_CONSOLE = 'console';
    private const CONT_PARAMS_META = 'paramsMeta';
    private const CONT_META = 'meta';

    private StoredQueryFormFactory $storedQueryFormFactory;

    private ServiceStoredQuery $serviceStoredQuery;

    private ServiceStoredQueryTag $serviceStoredQueryTag;

    private ServiceStoredQueryParameter $serviceStoredQueryParameter;

    private StoredQueryFactory $storedQueryFactory;

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void {
        $values = FormUtils::emptyStrToNull($form->getValues(), true);
        $connection = $this->serviceStoredQuery->getConnection();
        $connection->beginTransaction();

        $data = array_merge($values[self::CONT_CONSOLE], $values[self::CONT_META]);

        if ($this->create) {
            $model = $this->serviceStoredQuery->createNewModel($data);
        } else {
            $model = $this->model;
            $this->serviceStoredQuery->updateModel2($model, $data);
        }

        $this->saveTags($values[self::CONT_META]['tags'], $model->query_id);
        $this->saveParameters($values[self::CONT_PARAMS_META], $model->query_id);

        //$this->getPresenter()->clearSession();
        $connection->commit();
        $this->getPresenter()->flashMessage($this->create ? _('Query has been created') : _('Query has been edited'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    public function injectPrimary(
        StoredQueryFormFactory $storedQueryFormFactory,
        ServiceStoredQuery $serviceStoredQuery,
        ServiceStoredQueryTag $serviceStoredQueryTag,
        ServiceStoredQueryParameter $serviceStoredQueryParameter,
        StoredQueryFactory $storedQueryFactory
    ): void {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
        $this->storedQueryFactory = $storedQueryFactory;
    }

    protected function configureForm(Form $form): void {
        $group = $form->addGroup(_('SQL'));
        $console = $this->storedQueryFormFactory->createConsole($group);
        $form->addComponent($console, self::CONT_CONSOLE);
        $params = $this->storedQueryFormFactory->createParametersMetadata($group);
        $form->addComponent($params, self::CONT_PARAMS_META);

        $group = $form->addGroup(_('Metadata'));
        $metadata = $this->storedQueryFormFactory->createMetadata($group);
        $form->addComponent($metadata, self::CONT_META);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Execute'))
            ->setValidationScope(false);
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-success']);
        $submit->onClick[] = function (SubmitButton $button) {
            $this->handleComposeExecute($button->getForm());
        };
    }

    private function saveTags(array $tags, int $queryId): void {
        $this->serviceStoredQueryTag->getTable()->where([
            'query_id' => $queryId,
        ])->delete();
        foreach ($tags['tags'] as $tagTypeId) {
            $data = [
                'query_id' => $queryId,
                'tag_type_id' => $tagTypeId,
            ];
            $this->serviceStoredQueryTag->createNewModel($data);
        }
    }

    private function saveParameters(array $parameters, int $queryId): void {
        $this->serviceStoredQueryParameter->getTable()
            ->where(['query_id' => $queryId])->delete();

        foreach ($parameters as $paramMetaData) {
            $data = (array)$paramMetaData;
            $data['query_id'] = $queryId;
            $data = array_merge($data, ModelStoredQueryParameter::setInferDefaultValue($data['type'], $paramMetaData['default']));
            $this->serviceStoredQueryParameter->createNewModel($data);
        }
    }

    /**
     * @param AbstractModelSingle|ModelStoredQuery|null $model
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(?AbstractModelSingle $model): void {
        if (!is_null($model)) {
            $values = [];
            $values[self::CONT_CONSOLE] = $model;
            $values[self::CONT_META] = $model->toArray();
            $values[self::CONT_META]['tags'] = $model->getTags()->fetchPairs('tag_type_id', 'tag_type_id');
            $values[self::CONT_PARAMS_META] = [];
            foreach ($model->getParameters() as $parameter) {
                $paramData = $parameter->toArray();
                $paramData['default'] = $parameter->getDefaultValue();
                $values[self::CONT_PARAMS_META][] = $paramData;
            }
            if ($model->php_post_proc) {
                $this->flashMessage(_('Query result is still processed by PHP. Stick to the correct names of columns and parameters.'), BasePresenter::FLASH_WARNING);
            }
            $this->getForm()->setDefaults($values);
        }
    }

    protected function createComponentQueryResultsComponent(): ResultsComponent {
        $grid = new ResultsComponent($this->getContext());
        $grid->setShowParametrizeForm(false);
        return $grid;
    }

    /**
     * @param Form $form
     * @return void
     * @throws BadRequestException
     */
    private function handleComposeExecute(Form $form): void {
        $data = $form->getValues(true);
        $parameters = [];
        foreach ($data[self::CONT_PARAMS_META] as $paramMetaData) {
            $parameters[] = new StoredQueryParameter(
                $paramMetaData['name'],
                $paramMetaData['default'],
                ModelStoredQueryParameter::staticGetPDOType($paramMetaData['type'])
            );
        }
        $query = $this->storedQueryFactory->createQueryFromSQL(
            $this->getPresenter(),
            $data[self::CONT_CONSOLE]['sql'],
            $parameters
        );
        /** @var ResultsComponent $control */
        $control = $this->getComponent('queryResultsComponent');
        $control->setStoredQuery($query);
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.form.latte');
        $this->template->render();
    }
}
