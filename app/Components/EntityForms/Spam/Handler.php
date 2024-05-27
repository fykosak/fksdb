<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\FlagService;
use FKSDB\Models\ORM\Services\PersonHasFlagService;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nette\Utils\Html;

final class Handler
{
    private PersonService $personService;
    private PersonHistoryService $personHistoryService;
    private SchoolLabelService $schoolLabelService;
    private PersonHasFlagService $personHasFlagService;
    private FlagService $flagService;
    private LinkGenerator $linkGenerator;

    private ContestYearModel $contestYear;

    public MemoryLogger $logger;

    public function __construct(ContestYearModel $contestYear, Container $container)
    {
        $container->callInjects($this);
        $this->contestYear = $contestYear;
        $this->logger = new MemoryLogger();
    }

    public function inject(
        PersonService $personService,
        PersonHistoryService $personHistoryService,
        SchoolLabelService $schoolLabelService,
        PersonHasFlagService $personHasFlagService,
        FlagService $flagService,
        LinkGenerator $linkGenerator
    ): void {
        $this->personService = $personService;
        $this->personHistoryService = $personHistoryService;
        $this->schoolLabelService = $schoolLabelService;
        $this->personHasFlagService = $personHasFlagService;
        $this->flagService = $flagService;
        $this->linkGenerator = $linkGenerator;
    }

    public function storeSchool(
        string $schoolLabelKey,
        ?int $schoolId,
        ?SchoolLabelModel $schoolLabel
    ): SchoolLabelModel {
        if (!$schoolLabel) {
            // get possibly existing school
            $schoolLabel = $this->schoolLabelService->findByLabel($schoolLabelKey);
        }

        $created = false;
        if (!$schoolLabel) {
            $created = true;
        }

        // store school
        $schoolLabel = $this->schoolLabelService->storeModel([
            'school_label_key' => $schoolLabelKey,
            'school_id' => $schoolId ?? $schoolLabel->school_id
        ], $schoolLabel);

        if ($created) {
            $this->logger->log(new Message(
                Html::el('span')
                    ->addText(
                        sprintf(_('School %s created.'), $schoolLabel->school_label_key)
                    )
                    ->addText(' ')
                    ->addHtml($this->getSchoolEditLink($schoolLabel)),
                Message::LVL_SUCCESS
            ));
        }

        if ($schoolId) {
            // set new school_id for person history
            $query = $this->personHistoryService->getTable()->where('school_label_key', $schoolLabelKey);
            /** @var PersonHistoryModel $personHistory */
            foreach ($query as $personHistory) {
                $this->personHistoryService->storeModel([
                    'school_id' => $schoolId
                ], $personHistory);
            }
        }

        return $schoolLabel;
    }

    /**
     * @phpstan-param array{
     *      other_name:string,
     *      family_name:string,
     *      school_label_key:string,
     *      study_year_new:string,
     * } $values
     */
    public function storePerson(array $values, ?PersonHistoryModel $model): PersonHistoryModel
    {
        $created = false;
        if (!$model) {
            $created = true;
        }

        // store person
        /** @var PersonModel $personModel */
        $personModel = $this->personService->storeModel([
            'other_name' => $values['other_name'],
            'family_name' => $values['family_name']
        ], $model ? $model->person : null);

        // store person history
        $schoolLabel = $this->schoolLabelService->findByLabel($values['school_label_key']);

        /** @var PersonHistoryModel $personHistoryModel */
        $personHistoryModel = $this->personHistoryService->storeModel([
            'person_id' => $personModel->person_id,
            'ac_year' => $model ? $model->ac_year : $this->contestYear->ac_year,
            'study_year_new' => $values['study_year_new'],
            'school_label_key' => $values['school_label_key'],
            'school_id' => $schoolLabel ? $schoolLabel->school_id : null
        ], $model);

        // add spam flag
        if (!$personModel->hasFlag('source_spam')) {
            $this->personHasFlagService->storeModel([
                'person_id' => $personModel->person_id,
                'flag_id' => $this->flagService->findByFid('source_spam'),
                'ac_year' => $model ? $model->ac_year : $this->contestYear->ac_year
            ]);
        }

        if ($created) {
            $this->logger->log(new Message(
                Html::el('span')
                    ->addText(
                        sprintf(_('Person %s created.'), $personModel->getFullName())
                    )
                    ->addText(' ')
                    ->addHtml($this->getPersonEditLink($personHistoryModel)),
                Message::LVL_SUCCESS
            ));
        }

        return $personHistoryModel;
    }

    private function getPersonEditLink(PersonHistoryModel $personHistory): Html
    {
        $link = $this->linkGenerator->link(
            'Spam:Person:edit',
            [
                'contestId' => $this->contestYear->contest_id,
                'id' => $personHistory->person_history_id
            ]
        );

        return Html::el('a')
            ->setAttribute('target', '_blank')
            ->href($link)
            ->setText(_('Edit'));
    }

    private function getSchoolEditLink(SchoolLabelModel $school): Html
    {
        $link = $this->linkGenerator->link(
            'Spam:School:edit',
            [
                'contestId' => $this->contestYear->contest_id,
                'id' => $school->school_label_id
            ]
        );

        return Html::el('a')
            ->setAttribute('target', '_blank')
            ->href($link)
            ->setText(_('Edit'));
    }
}
