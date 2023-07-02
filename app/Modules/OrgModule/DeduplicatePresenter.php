<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonInfoService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Persons\Deduplication\DuplicateFinder;
use FKSDB\Models\Persons\Deduplication\Merger;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\PageTitle;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\Html;

class DeduplicatePresenter extends BasePresenter
{

    private PersonService $personService;
    private Merger $merger;
    private PersonInfoService $personInfoService;
    private PersonModel $trunkPerson;
    private PersonModel $mergedPerson;

    final public function injectQuarterly(
        PersonService $personService,
        Merger $merger,
        PersonInfoService $personInfoService
    ): void {
        $this->personService = $personService;
        $this->merger = $merger;
        $this->personInfoService = $personInfoService;
    }

    public function authorizedPerson(): bool
    {
        return $this->contestAuthorizator->isAllowed('person', 'list');
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedDontMerge(): bool
    {
        return $this->authorizedMerge();
    }

    /**
     * @throws NotFoundException
     */
    public function authorizedMerge(): bool
    {
        $trunkPerson = $this->personService->findByPrimary($this->getParameter('trunkId'));
        $mergedPerson = $this->personService->findByPrimary($this->getParameter('mergedId'));
        if (is_null($trunkPerson) || is_null($mergedPerson)) {
            throw new NotFoundException('Person does not exists');
        }
        $this->trunkPerson = $trunkPerson;
        $this->mergedPerson = $mergedPerson;
        return $this->contestAuthorizator->isAllowed($this->trunkPerson, 'merge') &&
            $this->contestAuthorizator->isAllowed($this->mergedPerson, 'merge');
    }

    public function titleMerge(): PageTitle
    {
        return new PageTitle(
            null,
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
        return new PageTitle(null, _('Duplicate persons'), 'fas fa-exchange');
    }

    public function actionDontMerge(): void
    {
        $trunkId = $this->getParameter('trunkId');
        $mergedId = $this->getParameter('mergedId');
        $mergedPI = $this->personInfoService->findByPrimary($mergedId);
        $this->personInfoService->storeModel(
            [
                'duplicates' => trim($mergedPI->duplicates . ",not-same($trunkId)", ','),
            ],
            $mergedPI
        );

        $trunkPI = $this->personInfoService->findByPrimary($trunkId);
        $this->personInfoService->storeModel(
            [
                'duplicates' => trim($trunkPI->duplicates . ",not-same($mergedId)", ','),
            ],
            $trunkPI
        );

        $this->flashMessage(_('Persons not merged.'), Message::LVL_SUCCESS);
        //$this->backLinkRedirect(true);
    }

    public function actionMerge(): void
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
    }

    protected function createComponentPersonsGrid(): PersonsGrid
    {
        $duplicateFinder = $this->createPersonDuplicateFinder();
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->personService->getTable()->where('person_id', array_keys($pairs));

        return new PersonsGrid($trunkPersons, $pairs, $this->getContext());
    }

    protected function createPersonDuplicateFinder(): DuplicateFinder
    {
        return new DuplicateFinder($this->personService, $this->getContext());
    }

    protected function createComponentMergeForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $this->updateMergeForm($form);
        $submitButton = $form->addSubmit('send', _('Merge persons'));
        $submitButton->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        $submitButton->onClick[] = fn(SubmitButton $button) => $this->handleMergeFormSuccess($button->getForm());

        $cancelButton = $form->addSubmit('cancel', _('Cancel'));
        $cancelButton->getControlPrototype()->addAttributes(['class' => 'btn-lg']);
        // $cancelButton->onClick[] = fn() => $this->backLinkRedirect(true);


        return $control;
    }

    private function handleMergeFormSuccess(Form $form): void
    {

        $values = $form->getValues();
        $values = FormUtils::emptyStrToNull2($values);

        $merger = $this->merger;
        $merger->setConflictResolution($values);
        $logger = new MemoryLogger();
        $merger->setLogger($logger);
        if ($merger->merge()) {
            $this->flashMessage(_('Persons successfully merged.'), Message::LVL_SUCCESS);
            FlashMessageDump::dump($logger, $this);
            // $this->backLinkRedirect(true);
        } else {
            $this->flashMessage(_('Manual conflict resolution is necessary.'), Message::LVL_INFO);
            $this->redirect('this'); //this is correct
        }
    }
}
