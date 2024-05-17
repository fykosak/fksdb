<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\Spam\SpamPersonModel;
use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use FKSDB\Models\ORM\Models\StudyYear;
use FKSDB\Models\ORM\Services\Spam\SpamPersonService;
use FKSDB\Models\ORM\Services\Spam\SpamSchoolService;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nette\Utils\Html;

class AjaxPersonFormComponent extends AjaxComponent {

    private ContestYearModel $contestYear;
    private SpamPersonService $spamPersonService;
    private SpamSchoolService $spamSchoolService;
    private LinkGenerator $linkGenerator;

    public function __construct(ContestYearModel $contestYear, Container $container)
    {
        parent::__construct($container, 'spam.person-form');
        $this->contestYear = $contestYear;
    }

    public function injectService(SpamPersonService $spamPersonService, SpamSchoolService $spamSchoolService, LinkGenerator $linkGenerator): void
    {
        $this->spamPersonService = $spamPersonService;
        $this->spamSchoolService = $spamSchoolService;
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
        $data['ac_year'] = $this->contestYear->ac_year;
        bdump($data);

        // add school if missing
        if (!$this->spamSchoolService->exists($data['spam_school_label'])) {
            /** @var SpamSchoolModel $schoolModel */
            $schoolModel = $this->spamSchoolService->storeModel([
                'spam_school_label' => $data['spam_school_label']
            ]);
            $this->getLogger()->log(new Message(
                Html::el('span')
                    ->addText(
                        sprintf(_('School %s created.'), $schoolModel->spam_school_label)
                    )
                    ->addText(' ')
                    ->addHtml($this->getSchoolEditLink($schoolModel)),
                Message::LVL_SUCCESS
            ));
        }

        /** @var SpamPersonModel $personModel */
        $personModel = $this->spamPersonService->storeModel($data);
        $this->getLogger()->log(new Message(
            Html::el('span')
                ->addText(
                    sprintf(_('Person %s created.'), $personModel->getFullName())
                )
                ->addText(' ')
                ->addHtml($this->getPersonEditLink($personModel)),
            Message::LVL_SUCCESS
        ));
        $this->sendAjaxResponse();
    }

    private function getPersonEditLink(SpamPersonModel $person): Html
    {
        $link = $this->linkGenerator->link(
            'Spam:Person:edit',
            [
                'contestId' => $this->contestYear->contest_id,
                'id' => $person->spam_person_id
            ]
        );

        return Html::el('a')
            ->setAttribute('target', '_blank')
            ->href($link)
            ->setText(_('Edit'));
    }

    private function getSchoolEditLink(SpamSchoolModel $school): Html
    {
        $link = $this->linkGenerator->link(
            'Spam:School:edit',
            [
                'contestId' => $this->contestYear->contest_id,
                'id' => $school->spam_school_label
            ]
        );

        return Html::el('a')
            ->setAttribute('target', '_blank')
            ->href($link)
            ->setText(_('Edit'));
    }
}
