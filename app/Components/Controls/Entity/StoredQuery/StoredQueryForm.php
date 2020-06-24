<?php

namespace FKSDB\Components\Controls\Entity\StoredQuery;

use FKSDB\Components\Controls\Entity\AbstractEntityFormControl;
use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Forms\Factories\StoredQueryFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\Modules\OrgModule\BasePresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\Utils\FormUtils;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Tracy\Debugger;

class StoredQueryForm extends AbstractEntityFormControl implements IEditEntityForm {
    const CONT_CONSOLE = 'console';
    const CONT_PARAMS_META = 'paramsMeta';
    const CONT_META = 'meta';

    /** @var StoredQueryFactory */
    private $storedQueryFormFactory;
    /** @var ServiceStoredQuery */
    private $serviceStoredQuery;
    /** @var ServiceStoredQueryTag */
    private $serviceStoredQueryTag;
    /** @var ServiceStoredQueryParameter */
    private $serviceStoredQueryParameter;

    /** @var ModelStoredQuery */
    private $model;

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    protected function handleFormSuccess(Form $form) {
        try {
            $this->create ? $this->handleCreateSuccess($form) : $this->handleEditSuccess($form);
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_DANGER);
        } catch (ModelException $exception) {
            $this->flashMessage(_('Chyba při ukládání do databáze.'), Message::LVL_DANGER);
            Debugger::log($exception);
        }
    }

    /**
     * @param StoredQueryFactory $storedQueryFormFactory
     * @param ServiceStoredQuery $serviceStoredQuery
     * @param ServiceStoredQueryTag $serviceStoredQueryTag
     * @param ServiceStoredQueryParameter $serviceStoredQueryParameter
     * @return void
     */
    public function injectPrimary(
        StoredQueryFactory $storedQueryFormFactory,
        ServiceStoredQuery $serviceStoredQuery,
        ServiceStoredQueryTag $serviceStoredQueryTag,
        ServiceStoredQueryParameter $serviceStoredQueryParameter
    ) {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->serviceStoredQueryTag = $serviceStoredQueryTag;
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
    }

    /**
     * @param Form $form
     * @return void
     */
    protected function configureForm(Form $form) {
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
            // $this->handleComposeExecute($button);
        };
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function handleEditSuccess(Form $form) {
        $this->handleSave($form, false);
        $this->getPresenter()->flashMessage(_('Query has been edited'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    private function handleCreateSuccess(Form $form) {
        $this->handleSave($form, true);
        $this->getPresenter()->flashMessage(_('Query has been created'), Message::LVL_SUCCESS);
        $this->getPresenter()->redirect('list');
    }

    /**
     * @param Form $form
     * @param bool $create
     * @return void
     * @throws BadRequestException TODO is still throw?
     */
    private function handleSave(Form $form, bool $create) {
        $values = FormUtils::emptyStrToNull($form->getValues(), true);
        $connection = $this->serviceStoredQuery->getConnection();
        $connection->beginTransaction();

        $data = array_merge($values[self::CONT_CONSOLE], $values[self::CONT_META]);

        if ($create) {
            $model = $this->serviceStoredQuery->createNewModel($data);
        } else {
            $model = $this->model;
            $this->serviceStoredQuery->updateModel2($model, $data);
        }

        $this->saveTags($values[self::CONT_META]['tags'], $model->query_id);
        $this->saveParameters($values[self::CONT_PARAMS_META], $model->query_id);

        //$this->getPresenter()->clearSession();
        $connection->commit();
    }

    /**
     * @param array $tags
     * @param int $queryId
     * @return void
     */
    private function saveTags(array $tags, int $queryId) {
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

    /**
     * @param array $parameters
     * @param int $queryId
     * @return void
     */
    private function saveParameters(array $parameters, int $queryId) {
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
     * @param AbstractModelSingle|ModelStoredQuery $model
     * @return void
     * @throws BadTypeException
     */
    public function setModel(AbstractModelSingle $model) {
        $this->model = $model;

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
        if ($model->getPostProcessing()) {
            $this->flashMessage(_('Výsledek dotazu je ještě zpracován v PHP. Dodržuj názvy sloupců a parametrů.'), BasePresenter::FLASH_WARNING);
        }
        $this->getForm()->setDefaults($values);
    }
}
