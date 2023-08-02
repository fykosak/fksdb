<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Models\StudyYear;
use Nette\Database\Explorer;
use Nette\Database\Row;
use Nette\DI\Container;
use Nette\SmartObject;

abstract class AESOPModel
{
    use SmartObject;

    protected const ID_SCOPE = 'fksdb.person_id';

    protected const END_YEAR = 'end-year';
    protected const RANK = 'rank';
    protected const POINTS = 'points';

    protected ContestYearModel $contestYear;

    protected Explorer $explorer;
    protected Container $container;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        $this->contestYear = $contestYear;
        $this->container = $container;
        $container->callInjects($this);
    }

    public function injectExplorer(Explorer $explorer): void
    {
        $this->explorer = $explorer;
    }

    protected function getDefaultParams(): array
    {
        return [
            'version' => 1,
            'event' => $this->getMask(),
            'year' => $this->contestYear->ac_year,
            'date' => date('Y-m-d H:i:s'),
            'errors-to' => 'it@fykos.cz',
            'id-scope' => self::ID_SCOPE,
        ];
    }

    private function formatSchool(?SchoolModel $school): ?string
    {
        if (!$school) {
            return null;
        }
        $countryISO = $school->address->country->alpha_2;
        if ($countryISO === 'cz') {
            return 'red-izo:' . $school->izo;
        }
        if ($countryISO === 'sk') {
            return 'sk:' . $school->izo;
        }
        return 'ufo';
    }

    /** @phpstan-ignore-next-line */
    public function formatResponse(array $params, iterable $data, array $cools): PlainTextResponse
    {
        $text = '';

        foreach ($params as $key => $value) {
            $text .= $key . "\t" . $value . "\n";
        }
        $text .= "\n";
        $text .= join("\t", $cools) . "\n";
        /** @var Row $datum */
        foreach ($data as $datum) {
            $text .= join("\t", iterator_to_array($datum->getIterator())) . "\n";
        }
        $response = new PlainTextResponse($text);
        $response->setName($this->getMask() . '.txt');
        return $response;
    }

    protected function getAESOPContestant(PersonModel $person): array
    {
        $postContact = $person->getActivePostContact();
        $history = $person->getHistoryByContestYear($this->contestYear);
        $school = $history->school;
        $spamFlag = $person->hasPersonFlag('spam_mff');
        return [
            'name' => $person->other_name,
            'surname' => $person->family_name,
            'id' => $person->person_id,
            'street' => $postContact->address->target,
            'town' => $postContact->address->city,
            'postcode' => $postContact->address->postal_code,
            'country' => $postContact->address->country->alpha_2,
            'fullname' => $person->display_name,
            'gender' => $person->gender->value,
            'school' => $this->formatSchool($school),
            'school-name' => $school->name_abbrev,
            'end-year' => $history->study_year
                ? $this->contestYear->getGraduationYear(StudyYear::tryFromLegacy($history->study_year))
                : null,
            'email' => $person->getInfo()->email,
            'spam-flag' => ($spamFlag->value === 1) ? 'Y' : (($spamFlag->value === 0) ? 'N' : null),
            'spam-date' => date('Y-m-d', $spamFlag->modified->getTimestamp()),
            'x-person_id' => $person->person_id,
            'x-birthplace' => $person->getInfo()->birthplace,
            'x-ac_year' => $history->ac_year,
        ];
    }

    abstract public function createResponse(): PlainTextResponse;

    abstract protected function getMask(): string;
}
