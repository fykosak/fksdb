<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use ModelPerson;
use Nette\Application\BadRequestException;
use ServicePerson;

/**
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ReactPersonProvider {

    const NULL_VALUE = ['hasValue' => false];
    const HAS_VALUE = ['hasValue' => true];

    /**
     * @var ServicePerson
     */
    private $servicePerson;


    function __construct(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param $email
     * @param $fields
     * @param $acYear
     * @return array
     * @throws BadRequestException
     */
    public function getPersonByEmail($email, $fields, $acYear) {
        $person = $this->servicePerson->findByEmail($email);
        if (!$person) {
            return $this->getDefaultData($email);
        }
        return $this->getParticipantData($person, $email, $fields, $acYear);
    }

    private function getDefaultData($email) {
        return [
            'email' => ['value' => $email, 'hasValue' => true,],
            'person.personId' => ['value' => 0, 'hasValue' => true,],
        ];
    }

    /**
     * @param $person ModelPerson
     * @param $email
     * @param array $fields
     * @param $acYear
     * @return array
     * @throws BadRequestException
     */
    private function getParticipantData(ModelPerson $person, $email, array $fields, $acYear) {

        $fieldsData = $this->getDefaultData($email);

        //$fields = ['personId', 'school', 'studyYear', 'idNumber', 'familyName', 'otherName'];
        foreach ($fields as $field) {

            /**
             * @var $personHistory \ModelPersonHistory
             */
            $personHistory = $person->getHistory($acYear);
            switch ($field) {
                case 'person.personId':
                    $fieldsData[$field] = ['value' => $person->person_id, 'hasValue' => true,];
                    break;
                case 'personHistory.schoolId':
                    if ($personHistory) {
                        $school = $personHistory->getSchool();
                        $fieldsData[$field] = !!$school ? self::HAS_VALUE : self::NULL_VALUE;
                    } else {
                        $fieldsData[$field] = self::NULL_VALUE;
                    }
                    break;
                case 'personHistory.studyYear':
                    if ($personHistory) {
                        $fieldsData[$field] = !!$personHistory->study_year ? self::HAS_VALUE : self::NULL_VALUE;
                    } else {
                        $fieldsData[$field] = self::NULL_VALUE;
                    }
                    break;
                case 'personInfo.idNumber':
                    $fieldsData[$field] = $person->getInfo()->id_number ? self::HAS_VALUE : self::NULL_VALUE;
                    break;
                case 'person.familyName':
                    $fieldsData[$field] = ['value' => $person->family_name, 'hasValue' => true,];
                    break;
                case 'person.otherName':
                    $fieldsData[$field] = ['value' => $person->other_name, 'hasValue' => true,];
                    break;
                default:
                    //throw new BadRequestException('pole ' . $field . ' nexistuje');
                    break;
            }
        }

        return ['fields' => $fieldsData];
    }

}
