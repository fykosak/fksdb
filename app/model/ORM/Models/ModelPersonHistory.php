<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property integer ac_year
 * @property integer school_id
 * @property string class
 * @property integer study_year
 */
class ModelPersonHistory extends AbstractModelSingle {
    /**
     * @return ModelPerson
     */
    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

    /**
     * @return ModelSchool
     */
    public function getSchool(): ModelSchool {
        return ModelSchool::createFromTableRow($this->ref(DbNames::TAB_SCHOOL, 'school_id'));
    }

    /**
     * @param int $acYear
     * @return ModelPersonHistory
     */
    public function extrapolate(int $acYear): ModelPersonHistory {
        $diff = $acYear - $this->ac_year;
        $data = [
            'ac_year' => $acYear,
            'school_id' => $this->school_id,
            'class' => $this->extrapolateClass($this->class, $diff),
            'study_year' => $this->extrapolateStudyYear($this->study_year, $diff)
        ];
        $result = new self([], $this->getTable());
        foreach ($data as $key => $value) {
            $result->$key = $value; // this is workaround to properly set modfified flag
        }
        return $result;
    }

    /**
     * @var string[][]
     */
    private static $classProgress = [
        ['prima', 'sekunda', 'tercie', 'kvarta', 'kvinta', 'sexta', 'septima', 'oktáva'],
        ['I.', 'II.', 'III.', 'IV.', 'V.', 'VI.', 'VII.', 'VIII.'],
        ['1.', '2.', '3.', '4.', '5.', '6.', '7.', '8.'],
    ];

    /**
     * @param $class
     * @param $diff
     * @return null|string|string[]
     */
    private function extrapolateClass(string $class = null, int $diff = 0) {
        if (!$class) {
            return null;
        }
        foreach (self::$classProgress as $sequence) {
            $pattern = '/(' . implode('|', array_map('preg_quote', $sequence)) . ')/i';
            $class = preg_replace_callback($pattern, function ($matches) use ($sequence, $diff) {
                $idx = array_search(mb_strtolower($matches[0]), $sequence);
                $newIdx = $idx + $diff;
                if ($newIdx > count($sequence) - 1) {
                    return $matches[1];
                } else {
                    return $sequence[$newIdx];
                }
            }, $class);
        }
        return $class;
    }

    /**
     * @param $studyYear
     * @param $diff
     * @return int|null
     */
    private function extrapolateStudyYear(int $studyYear = null, int $diff = 0) {
        if (!$studyYear) {
            return null;
        }
        $result = null;
        if ($studyYear >= 6 && $studyYear <= 9) {
            $result = $studyYear + $diff;
            if ($result > 9) {
                $result -= 9;
                if ($result > 4) {
                    $result = null;
                }
            }
        } else if ($studyYear >= 1 && $studyYear <= 4) {
            $result = $studyYear + $diff;
            if ($result > 4) {
                $result = null;
            }
        }

        return $result;
    }

}

