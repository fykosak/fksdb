<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotFoundException;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePerson;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use FKSDB\Models\Persons\Deduplication\DuplicateFinder;
use FKSDB\Models\Persons\Deduplication\Merger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Models\Utils\FormUtils;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\Html;

class DeduplicatePresenter extends BasePresenter
{

    private ServicePerson $servicePerson;
    private Merger $merger;
    private ServicePersonInfo $servicePersonInfo;
    private ModelPerson $trunkPerson;
    private ModelPerson $mergedPerson;

    final public function injectQuarterly(
        ServicePerson $servicePerson,
        Merger $merger,
        ServicePersonInfo $servicePersonInfo
    ): void {
        $this->servicePerson = $servicePerson;
        $this->merger = $merger;
        $this->servicePersonInfo = $servicePersonInfo;
    }

    public function authorizedPerson(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowedForAnyContest('person', 'list'));
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedDontMerge(int $trunkId, int $mergedId): void
    {
        $this->authorizedMerge($trunkId, $mergedId);
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedMerge(int $trunkId, int $mergedId): void
    {
        $trunkPerson = $this->servicePerson->findByPrimary($trunkId);
        $mergedPerson = $this->servicePerson->findByPrimary($mergedId);
        if (is_null($trunkPerson) || is_null($mergedPerson)) {
            throw new NotFoundException('Person does not exists');
        }
        $this->trunkPerson = $trunkPerson;
        $this->mergedPerson = $mergedPerson;
        $authorized = $this->contestAuthorizator->isAllowedForAnyContest($this->trunkPerson, 'merge') &&
            $this->contestAuthorizator->isAllowedForAnyContest($this->mergedPerson, 'merge');
        $this->setAuthorized($authorized);
    }

    public function titleMerge(): PageTitle
    {
        return new PageTitle(
            sprintf(
                _('Merging persons %s (%d) and %s (%d)'),
                $this->trunkPerson->getFullName(),
                $this->trunkPerson->person_id,
                $this->mergedPerson->getFullName(),
                $this->mergedPerson->person_id
            )
        );
    }

    public function titlePerson(): PageTitle
    {
        return new PageTitle(_('Duplicate persons'), 'fa fa-exchange');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function actionDontMerge(int $trunkId, int $mergedId): void
    {
        $mergedPI = $this->servicePersonInfo->findByPrimary($mergedId);
        $mergedData = ['duplicates' => trim($mergedPI->duplicates . ",not-same($trunkId)", ',')];
        $this->servicePersonInfo->updateModel($mergedPI, $mergedData);

        $trunkPI = $this->servicePersonInfo->findByPrimary($trunkId);
        $trunkData = ['duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ',')];
        $this->servicePersonInfo->updateModel($trunkPI, $trunkData);

        $this->flashMessage(_('Persons not merged.'), Message::LVL_SUCCESS);
        $this->backLinkRedirect(true);
    }

    public function actionMerge(int $trunkId, int $mergedId): void
    {
        $this->merger->setMergedPair($this->trunkPerson, $this->mergedPerson);
        $this->updateMergeForm($this->getComponent('mergeForm')->getForm());
    }

    private function updateMergeForm(Form $form): void
    {
        $conflicts = $this->merger->getConflicts();
        foreach ($conflicts as $table => $pairs) {
            $form->addGroup($table);
            $tableContainer = new ContainerWithOptions($this->getContext());

            $form->addComponent($tableContainer, $table);

            foreach ($pairs as $pairId => $data) {
                if (!isset($data[Merger::IDX_TRUNK])) {
                    continue;
                }
                $pairSuffix = '';
                if (count($pairs) > 1) {
                    $pairSuffix = " ($pairId)";
                }
                $pairContainer = new ContainerWithOptions($this->getContext());
                $tableContainer->addComponent($pairContainer, $pairId);
                $pairContainer->setOption('label', \str_replace('_', ' ', $table));
                foreach ($data[Merger::IDX_TRUNK] as $column => $value) {
                    if (
                        isset($data[Merger::IDX_RESOLUTION])
                        && array_key_exists(
                            $column,
                            $data[Merger::IDX_RESOLUTION]
                        )
                    ) {
                        $default = $data[Merger::IDX_RESOLUTION][$column];
                    } else {
                        $default = $value; // default is trunk
                    }

                    $textElement = $pairContainer->addText($column, $column . $pairSuffix)
                        ->setDefaultValue($default);

                    $description = Html::el('div');

                    $trunk = Html::el('div');
                    $trunk->addAttributes(['class' => 'mergeSource']);
                    $trunk->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($value);
                    $trunk->addText(_('Trunk') . ': ');
                    $trunk->addText($elVal);
                    $elVal->addAttributes(['class' => 'value']);

                    $description->addHtml($trunk);

                    $merged = Html::el('div');
                    $merged->addAttributes(['class' => 'mergeSource']);
                    $merged->data['field'] = $textElement->getHtmlId();
                    $elVal = Html::el('span');
                    $elVal->setText($data[Merger::IDX_MERGED][$column]);
                    $elVal->addAttributes(['class' => 'value']);
                    $merged->addText(_('Merged') . ': ');
                    $merged->addText($elVal);
                    $description->addHtml($merged);

                    $textElement->setOption('description', $description);
                }
            }
        }
        $this->registerJSFile('js/mergeForm.js');
    }

    protected function createComponentPersonsGrid(): PersonsGrid
    {
        $duplicateFinder = $this->createPersonDuplicateFinder();
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));

        return new PersonsGrid($trunkPersons, $pairs, $this->getContext());
    }

    protected function createPersonDuplicateFinder(): DuplicateFinder
    {
        return new DuplicateFinder($this->servicePerson, $this->getContext());
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentMergeForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $this->updateMergeForm($form);
        $submitButton = $form->addSubmit('send', _('Merge persons'));
        $submitButton->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $submitButton->onClick[] = function (SubmitButton $button) {
            $this->handleMergeFormSuccess($button->getForm());
        };
        $cancelButton = $form->addSubmit('cancel', _('Cancel'));
        $cancelButton->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $cancelButton->onClick[] = function () {
            $this->backLinkRedirect(true);
        };

        return $control;
    }

    /**
     * @throws \ReflectionException
     * @throws BadTypeException
     */
    private function handleMergeFormSuccess(Form $form): void
    {

        $values = $form->getValues();
        $values = FormUtils::emptyStrToNull($values);

        $merger = $this->merger;
        $merger->setConflictResolution($values);
        $logger = new MemoryLogger();
        $merger->setLogger($logger);
        if ($merger->merge()) {
            $this->flashMessage(_('Persons successfully merged.'), self::FLASH_SUCCESS);
            FlashMessageDump::dump($logger, $this);
            $this->backLinkRedirect(true);
        } else {
            $this->flashMessage(_('Manual conflict resolution is necessary.'), self::FLASH_INFO);
            $this->redirect('this'); //this is correct
        }
    }
}
