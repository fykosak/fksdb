<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Spam;

use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\Html;

class AjaxPersonFormComponent extends AjaxComponent
{

    private ContestYearModel $contestYear;
    private Handler $handler;

    public function __construct(ContestYearModel $contestYear, Container $container)
    {
        parent::__construct($container, 'spam.person-form');
        $this->contestYear = $contestYear;
        $this->handler = new Handler($contestYear, $this->container);
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

    /**
     * @throws InvalidLinkException
     */
    protected function configure(): void
    {
        $this->addAction('save', 'save!');
    }

    public function handleSave(): void
    {
        /**
         * @var array{
         *      other_name:string,
         *      family_name:string,
         *      school_label_key:string,
         *      study_year_new:string,
         * } $data
         */
        $data = (array)json_decode($this->getHttpRequest()->getRawBody());

        $this->handler->storeSchool($data['school_label_key'], null, null);
        $this->handler->storePerson($data, null);

        foreach ($this->handler->logger->getMessages() as $message) {
            $this->getLogger()->log($message);
        }

        $this->sendAjaxResponse();
    }
}
