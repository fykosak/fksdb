<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelPersonHistory extends AbstractModelSingle {

    /**
     * @return ModelPerson
     */
    public function getPerson() {
        return ModelPerson::createFromTableRow($this->ref(DbNames::TAB_PERSON, 'person_id'));
    }

    /**
     * @param int $acYear
     * @return ModelPersonHistory
     */
    public function extrapolate($acYear) {
        $diff = $acYear - $this->ac_year;
        $data = array(
            'ac_year' => $acYear,
            'school_id' => $this->school_id,
            'class' => $this->extrapolateClass($this->class, $diff),
            'study_year' => $this->extrapolateStudyYear($this->study_year, $diff)
        );
        $result = new self(array(), $this->getTable());
        foreach($data as $key => $value){
            $result->$key = $value; // this is workaround to properly set modfified flag
        }
        return $result;
    }

    private static $classProgress = array(
        array('prima', 'sekunda', 'tercie', 'kvarta', 'kvinta', 'sexta', 'septima', 'oktáva'),
        array('I.', 'II.', 'III.', 'IV.', 'V.', 'VI.', 'VII.', 'VIII.'),
        array('1.', '2.', '3.', '4.', '5.', '6.', '7.', '8.'),
    );

    private function extrapolateClass($class, $diff) {
        if (!$class) {
            return null;
        }
        foreach (self::$classProgress as $sequence) {
            $pattern = '/(' . implode('|', array_map('preg_quote', $sequence)) . ')/i';
            $class = preg_replace_callback($pattern, function($matches) use($sequence, $diff) {
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

    private function extrapolateStudyYear($studyYear, $diff) {
        if (!$studyYear) {
            return null;
        }
        if ($studyYear >= 6 && $studyYear <= 9) {
            $result = $studyYear + $diff;
            if ($result > 9) {
                $result -= 9;
            }
        } else if ($studyYear >= 1 && $studyYear <= 4) {
            $result = $studyYear + $diff;
        }

        return $result;
    }

}

