<?php

namespace FKSDB\Events\Spec\Sous;

use FKSDB\ORM\ModelsMulti\Events\ModelMSousParticipant;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @deprecated since 34 year is not supported
 */
class Heuristics /*extends StoredQueryPostProcessing */{ /* uncomment to use */

    const RESERVE_1 = 8;
    const ABS_INV = 3;
    const CAT_COUNT = 4;
    const P_4 = 1; // o kolik méně se pozývá čtvrťáků než ostatních
    const RULE_1 = 1;
    const RULE_2 = 2;
    const RULE_3M = '3M';
    const RULE_3F = '3F';
    const RULE_3I = '3*';
    const RULE4M2 = '42M';
    const RULE4F2 = '42F';
    const RULE4MW = '4WM';
    const RULE4FW = '4WF';

    /**
     * @return string
     */
    public function getDescription(): string {
        return 'Z výsledkovky vybere zvance a náhradníky na soustředění (http://wiki.fykos.cz/fykos:soustredeni:zasady:heuristikazvani).
            Hierarchický kód určuje pravidlo a případně podpravidlo, dle nějž je osoba zvaná/náhradníkovaná.';
    }

    /**
     * @param \PDOStatement $data
     * @return \Traversable|array|\ArrayIterator
     */
    public function processData(\PDOStatement $data) {

        $result = iterator_to_array($data);
        $p = $this->findP($result);

        $y = $k = $h = 0;
        $z = $this->parameters['par_z'];
        $n = $this->parameters['par_n'];
        /*
         * Rule no. 1
         */
        foreach ($result as $row) {
            if (!$this->checkInvMin($row)) {
                break;
            }
            if ($this->inviting($row, $p)) {
                $row['invited'] = self::RULE_1;
                $y++;
                ($row['gender'] == 'M') ? $k++ : $h++;
            }
        }


        /*
         * Rule no. 2
         */
        $absInv = 0;
        foreach ($result as $row) {
            if (!$this->checkInvMin($row)) {
                break;
            }
            if (!$row['invited']) {
                $row['invited'] = self::RULE_2;
                $y += 1;
                $k += ($row['gender'] == 'M') ? 1 : 0;
                $h += ($row['gender'] == 'F') ? 1 : 0;
                $absInv++;
            }
            if ($absInv >= self::ABS_INV) {
                break;
            }
        }

        /*
         * Rule no. 3
         */
        $success = true; // this avoids infinite loops
        while ($y < $z && $success) {
            if ($k / $h > 2) { // too many boys
                $searchFor = 'F';
                $rule = self::RULE_3F;
            } elseif ($k / ($h - 1) < 1) { // too many girls
                $searchFor = 'M';
                $rule = self::RULE_3M;
            } else {
                $searchFor = '*';
                $rule = self::RULE_3I;
            }
            $success = false;
            foreach ($result as $row) {
                if (!$this->checkInvMin($row)) {
                    break;
                }
                if (!$row['invited'] && ($searchFor == '*' || $row['gender'] == $searchFor)) {
                    $row['invited'] = $rule;
                    $y += 1;
                    $k += ($row['gender'] == 'M') ? 1 : 0;
                    $h += ($row['gender'] == 'F') ? 1 : 0;
                    $success = true;
                    break;
                }
            }
        }

        /*
         * Rule no. 4
         */
        $w = 0;
        foreach ($result as $row) {
            if (!$this->checkSpMin($row)) {
                break;
            }
            if ($row['invited']) {
                continue;
            }
            if ($row['gender'] == 'F') {
                $w += 1;
            }
        }
        if ($w < ceil($n / 2)) { // not enough girls (the code assumes reverse is never true)
            $spareGirls = 0;
            foreach ($result as $row) {
                if ($row['invited']) {
                    continue;
                }
                if (!$this->checkSpMin($row)) {
                    break;
                }
                if ($row['gender'] == 'F') {
                    $row['spare'] = self::RULE4FW;
                    $spareGirls += 1;
                }
                if ($spareGirls >= $w) {
                    break;
                }
            }

            $spareAll = $spareGirls;
            foreach ($result as $row) {
                if ($row['invited']) {
                    continue;
                }
                if (!$this->checkSpMin($row)) {
                    break;
                }
                if ($row['gender'] == 'M') {
                    $row['spare'] = self::RULE4MW;
                    $spareAll += 1;
                }
                if ($spareAll >= $n) {
                    break;
                }
            }
        } else {
            $nK = floor($n / 2);
            $nH = ceil($n / 2);
            $spareK = $spareH = 0;
            foreach ($result as $row) {
                if ($row['invited']) {
                    continue;
                }
                if (!$this->checkSpMin($row)) {
                    break;
                }
                if ($row['gender'] == 'M' && $spareK < $nK) {
                    $row['spare'] = self::RULE4M2;
                    $spareK += 1;
                } elseif ($row['gender'] == 'F' && $spareH < $nH) {

                    $row['spare'] = self::RULE4F2;
                    $spareH += 1;
                }
                if ($spareH + $spareK >= $n) {
                    break;
                }
            }
        }
        /*
         * Prepare application states
         */
        foreach ($result as $row) {
            if ($row['invited']) {
                $row['status'] = ModelMSousParticipant::STATE_AUTO_INVITED;
            } elseif ($row['spare']) {
                $row['status'] = ModelMSousParticipant::STATE_AUTO_SPARE;
            }
        }

        return $result;
    }

    private function findP(array $data): float {
        $parameterZ = $this->parameters['par_z'];
        $parameterP = ceil(($parameterZ - self::RESERVE_1) / self::CAT_COUNT) + 1;
        do {
            $parameterY = 0;
            $parameterP -= 1;
            foreach ($data as $row) {
                if ($this->inviting($row, $parameterP) && $this->checkInvMin($row)) {
                    $parameterY += 1;
                }
            }
        } while ($parameterY > $parameterZ - self::RESERVE_1);
        return $parameterP;
    }

    private function inviting(array $row, float $parameterP): bool {
        return $row['category'] == 4 ? ($row['cat_rank'] <= $parameterP - self::P_4) : ($row['cat_rank'] <= $parameterP);
    }

    private function checkInvMin(array $row): bool {
        return $row['points'] >= $this->parameters['min_z'];
    }

    private function checkSpMin(array $row): bool {
        return $row['points'] >= $this->parameters['min_n'];
    }
}
