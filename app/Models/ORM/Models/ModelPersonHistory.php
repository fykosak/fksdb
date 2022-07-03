<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int ac_year
 * @property-read int|null school_id
 * @property-read ActiveRow|null school
 * @property-read ActiveRow person
 * @property-read int person_id
 * @property-read string class
 * @property-read int study_year
 */
class ModelPersonHistory extends Model
{

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getSchool(): ?ModelSchool
    {
        return $this->school ? ModelSchool::createFromActiveRow($this->school) : null;
    }

    public function extrapolate(int $acYear): ModelPersonHistory
    {
        $diff = $acYear - $this->ac_year;
        $data = [
            'ac_year' => $acYear,
            'school_id' => $this->school_id,
            'class' => $this->extrapolateClass($this->class, $diff),
            'study_year' => $this->extrapolateStudyYear($this->study_year, $diff),
        ];

        $tmpData = [];
        foreach ($data as $key => $value) {
            $tmpData[$key] = $value; // this is workaround to properly set modfified flag
        }
        return new self($tmpData, $this->getTable());
    }

    /** @var string[][] */
    private static array $classProgress = [
        ['prima', 'sekunda', 'tercie', 'kvarta', 'kvinta', 'sexta', 'septima', 'oktáva'],
        ['I.', 'II.', 'III.', 'IV.', 'V.', 'VI.', 'VII.', 'VIII.'],
        ['1.', '2.', '3.', '4.', '5.', '6.', '7.', '8.'],
    ];

    private function extrapolateClass(?string $class = null, int $diff = 0): ?string
    {
        if (!$class) {
            return null;
        }
        foreach (self::$classProgress as $sequence) {
            $pattern = '/(' . implode('|', array_map('preg_quote', $sequence)) . ')/i';
            $class = preg_replace_callback(
                $pattern,
                function ($matches) use ($sequence, $diff): string {
                    $idx = array_search(mb_strtolower($matches[0]), $sequence);
                    $newIdx = $idx + $diff;
                    if ($newIdx > count($sequence) - 1) {
                        return $matches[1];
                    } else {
                        return $sequence[$newIdx];
                    }
                },
                $class
            );
        }
        return $class;
    }

    private function extrapolateStudyYear(?int $studyYear = null, int $diff = 0): ?int
    {
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
        } elseif ($studyYear >= 1 && $studyYear <= 4) {
            $result = $studyYear + $diff;
            if ($result > 4) {
                $result = null;
            }
        }
        return $result;
    }
}
