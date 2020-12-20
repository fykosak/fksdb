<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Models\DBReflection\ColumnFactories\AbstractColumnException;
use FKSDB\Models\DBReflection\OmittedControlException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\Messages\Message;
use FKSDB\Modules\OrgModule\BasePresenter;
use FKSDB\Modules\OrgModule\StoredQueryPresenter;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\Models\StoredQuery\StoredQueryFactory;
use FKSDB\Models\StoredQuery\StoredQueryParameter;
use FKSDB\Models\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * Class StoredQueryForm
 * @author Michal Červeňák <miso@fykos.cz>
 * @method StoredQueryPresenter getPresenter($throw = true)
 * @property ModelStoredQuery $model
 */
class StoredQueryFormComponent extends AbstractEntityFormComponent {
    private const CONT_SQL = 'sql';
    private const CONT_PARAMS = 'params';
    private const CONT_MAIN = 'main';

    private StoredQueryFormFactory $storedQueryFormFactory;
    private ServiceStoredQuery $serviceStoredQuery;
    private ServiceStoredQueryTag $serviceStoredQueryTag;
    private ServiceStoredQueryParameter $serviceStoredQueryParameter;
    private StoredQueryFactory $storedQueryFactory;

    final public function injectPrimary(
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

        $data = array_merge($values[self::CONT_SQL], $values[self::CONT_MAIN]);

        if (isset($this->model)) {
            $model = $this->model;
            $this->serviceStoredQuery->updateModel2($model, $data);
        } else {
            $model = $this->serviceStoredQuery->createNewModel($data);
        }

        $this->saveTags($values[self::CONT_MAIN]['tags'], $model->query_id);
        $this->saveParameters($values[self::CONT_PARAMS], $model->query_id);

        $connection->commit();
        $this->getPresenter()->flashMessage(!isset($this->model) ? _('Query has been created') : _('Query has been edited'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param Form $form
     * @return void
     * @throws BadTypeException
     * @throws AbstractColumnException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void {
        $group = $form->addGroup(_('SQL'));
        $form->addComponent($this->storedQueryFormFactory->createConsole($group), self::CONT_SQL);

        $group = $form->addGroup(_('Parameters'));
        $form->addComponent($this->storedQueryFormFactory->createParametersMetadata($group), self::CONT_PARAMS);

        $group = $form->addGroup(_('Metadata'));
        $form->addComponent($this->storedQueryFormFactory->createMetadata($group), self::CONT_MAIN);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Execute'))
            ->setValidationScope(null);
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
     * @return void
     * @throws BadTypeException
     */
    protected function setDefaults(): void {
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
            if ($this->model->php_post_proc) {
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

    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.storedQuery.latte';
    }
}
