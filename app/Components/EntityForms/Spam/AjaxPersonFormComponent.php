<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Models\Spam\SpamPersonModel;
use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Services\FlagService;
use FKSDB\Models\ORM\Services\PersonHasFlagService;
use FKSDB\Models\ORM\Services\PersonHistoryService;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use FKSDB\Models\ORM\Services\Spam\SpamSchoolService;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nette\Utils\Html;

class AjaxPersonFormComponent extends AjaxComponent
{

    private ContestYearModel $contestYear;
    private PersonService $personService;
    private PersonHistoryService $personHistoryService;
    private SchoolLabelService $schoolLabelService;
    private PersonHasFlagService $personHasFlagService;
    private FlagService $flagService;
    private LinkGenerator $linkGenerator;

    public function __construct(ContestYearModel $contestYear, Container $container)
    {
        parent::__construct($container, 'spam.person-form');
        $this->contestYear = $contestYear;
    }

    public function injectService(
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

    /**
     * @phpstan-return array{
     *     studyYears:array<string,array<string,string>>
     * }
     */
    protected function getData(): array
    {
        return [
            'studyYears' => $this->getStudyYears(),
            'acYear' => $this->contestYear->ac_year
        ];
    }

    /**
     * @phpstan-return array<string,array<string,string>>
     */
    private function getStudyYears(): array
    {
        $primarySchoolStudyYears = [];
        foreach (StudyYear::getPrimarySchoolCases() as $studyYear) {
            $primarySchoolStudyYears[$studyYear->value] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $studyYear->getGraduationYear($this->contestYear->ac_year)
            );
        }

        $highSchoolStudyYears = [];
        foreach (StudyYear::getHighSchoolCases() as $studyYear) {
            $highSchoolStudyYears[$studyYear->value] = sprintf(
                _('grade %d (expected graduation in %d)'),
                $studyYear->numeric(),
                $studyYear->getGraduationYear($this->contestYear->ac_year)
            );
        }

        return [
            _('primary school') => $primarySchoolStudyYears,
            _('high school') => $highSchoolStudyYears,
        ];
    }

    protected function configure(): void
    {
        $this->addAction('save', 'save!');
    }

    public function handleSave(): void
    {
        $data = (array)json_decode($this->getHttpRequest()->getRawBody());

        // add school if missing
        if (!$this->schoolLabelService->exists($data['school_label_key'])) {
            /** @var SchoolLabelModel $schoolLabelModel */
            $schoolLabelModel = $this->schoolLabelService->storeModel([
                'school_label_key' => $data['school_label_key']
            ]);
            $this->getLogger()->log(new Message(
                Html::el('span')
                    ->addText(
                        sprintf(_('School %s created.'), $schoolLabelModel->school_label_key)
                    )
                    ->addText(' ')
                    ->addHtml($this->getSchoolEditLink($schoolLabelModel)),
                Message::LVL_SUCCESS
            ));
        }

        // create person
        /** @var PersonModel $personModel */
        $personModel = $this->personService->storeModel([
            'other_name' => $data['other_name'],
            'family_name' => $data['family_name']
        ]);

        // add person history
        /** @var PersonModel $personModel */
        $personHistoryModel = $this->personHistoryService->storeModel([
            'person_id' => $personModel->person_id,
            'ac_year' => $this->contestYear->ac_year,
            'study_year_new' => $data['study_year_new'],
            'school_label_key' => $data['school_label_key']
        ]);

        // add spam flag
        $this->personHasFlagService->storeModel([
            'person_id' => $personModel->person_id,
            'flag_id' => $this->flagService->findByFid('source_spam'),
            'ac_year' => $this->contestYear->ac_year
        ]);

        $this->getLogger()->log(new Message(
            Html::el('span')
                ->addText(
                    sprintf(_('Person %s created.'), $personModel->getFullName())
                )
                ->addText(' ')
                ->addHtml($this->getPersonEditLink($personHistoryModel)),
            Message::LVL_SUCCESS
        ));

        $this->sendAjaxResponse();
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
                'id' => $school->school_label_key
            ]
        );

        return Html::el('a')
            ->setAttribute('target', '_blank')
            ->href($link)
            ->setText(_('Edit'));
    }
}
