<?php

namespace FKSDB\Models\WebService\AESOP\Models;

use FKSDB\Models\Exports\Formats\PlainTextResponse;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelSchool;
use Nette\Database\Explorer;
use Nette\Database\Row;
use Nette\DI\Container;
use Nette\SmartObject;

abstract class AESOPModel {

    use SmartObject;

    protected const ID_SCOPE = 'fksdb.person_id';

    protected const END_YEAR = 'end-year';
    protected const RANK = 'rank';
    protected const POINTS = 'points';

    protected ModelContestYear $contestYear;

    protected Explorer $explorer;

    public function __construct(Container $container, ModelContestYear $contestYear) {
        $this->contestYear = $contestYear;
        $container->callInjects($this);
    }

    public function injectExplorer(Explorer $explorer): void {
        $this->explorer = $explorer;
    }

    protected function getDefaultParams(): array {
        return [
            'version' => 1,
            'event' => $this->getMask(),
            'year' => $this->contestYear->ac_year,
            'date' => date('Y-m-d H:i:s'),
            'errors-to' => 'it@fykos.cz',
            'id-scope' => self::ID_SCOPE,
        ];
    }

    private function formatSchool(?ModelSchool $school): ?string {
        if (!$school) {
            return null;
        }
        $countryISO = $school->getAddress()->getRegion()->country_iso;
        if ($countryISO === 'cz') {
            return 'red-izo:' . $school->izo;
        }
        if ($countryISO === 'sk') {
            return 'sk:' . $school->izo;
        }
        return 'ufo';
    }

    public function formatResponse(array $params, iterable $data, array $cools): PlainTextResponse {
        $text = '';

        foreach ($params as $key => $value) {
            $text .= $key . "\t" . $value . "\n";
        }
        $text .= "\n";
        /** @var Row $datum */
        $text .= join("\t", $cools) . "\n";
        foreach ($data as $datum) {
            $text .= join("\t", iterator_to_array($datum->getIterator())) . "\n";
        }
        $response = new PlainTextResponse($text);
        $response->setName($this->getMask() . '.txt');
        return $response;
    }

    protected function getAESOPContestant(ModelPerson $person): array {
        $postContact = $person->getPermanentPostContact(false);
        $history = $person->getHistoryByContestYear($this->contestYear);
        $school = $history->getSchool();
        $spamFlag = $person->getPersonHasFlag('spam_mff');
        return [
            'name' => $person->other_name,
            'surname' => $person->family_name,
            'id' => $person->person_id,
            'street' => $postContact->getAddress()->target,
            'town' => $postContact->getAddress()->city,
            'postcode' => $postContact->getAddress()->postal_code,
            'country' => $postContact->getAddress()->getRegion()->country_iso,
            'fullname' => $person->display_name,
            'gender' => $person->gender,
            'school' => $this->formatSchool($school),
            'school-name' => $school->name_abbrev,
            'end-year' => ($history->study_year < 5 && $history->study_year > 0) ?
                ($history->ac_year + 5 - $history->study_year) :
                (($history->study_year > 5 && $history->study_year < 10) ?
                    ($history->ac_year + 14 - $history->study_year)
                    : null
                ),
            'email' => $person->getInfo()->email,
            'spam-flag' => ($spamFlag->value === 1) ? 'Y' : (($spamFlag->value === 0) ? 'N' : null),
            'spam-date' => date('Y-m-d', $spamFlag->modified),
            'x-person_id' => $person->person_id,
            'x-birthplace' => $person->getInfo()->birthplace,
            'x-ac_year' => $history->ac_year,
        ];
    }

    abstract public function createResponse(): PlainTextResponse;

    abstract protected function getMask(): string;

}
