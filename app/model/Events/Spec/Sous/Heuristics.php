<?php

namespace Events\Spec\Sous;

use FKSDB\ORM\ModelsMulti\Events\ModelMSousParticipant;
use Exports\StoredQueryPostProcessing;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Heuristics extends StoredQueryPostProcessing {

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
     * @return mixed|string
     */
    public function getDescription() {
        return 'Z výsledkovky vybere zvance a náhradníky na soustředění (http://wiki.fykos.cz/fykos:soustredeni:zasady:heuristikazvani).
            Hierarchický kód určuje pravidlo a případně podpravidlo, dle nějž je osoba zvaná/náhradníkovaná.
';
    }

    /**
     * @param $data
     * @return array|mixed
     */
    public function processData($data) {
        $result = iterator_to_array($data);
        $P = $this->findP($result);

        $Y = $K = $H = 0;
        $Z = $this->parameters['par_z'];
        $N = $this->parameters['par_n'];

        /*
         * Rule no. 1
         */
        foreach ($result as $row) {
            if (!$this->checkInvMin($row)) {
                break;
            }
            if ($this->inviting($row, $P)) {
                $row['invited'] = self::RULE_1;
                $Y += 1;
                $K += ($row['gender'] == 'M') ? 1 : 0;
                $H += ($row['gender'] == 'F') ? 1 : 0;
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
                $Y += 1;
                $K += ($row['gender'] == 'M') ? 1 : 0;
                $H += ($row['gender'] == 'F') ? 1 : 0;
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
        while ($Y < $Z && $success) {
            if ($K / $H > 2) { // too many boys
                $searchFor = 'F';
                $rule = self::RULE_3F;
            } elseif ($K / ($H - 1) < 1) { // too many girls
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
                    $Y += 1;
                    $K += ($row['gender'] == 'M') ? 1 : 0;
                    $H += ($row['gender'] == 'F') ? 1 : 0;
                    $success = true;
                    break;
                }
            }
        }

        /*
         * Rule no. 4
         */
        $W = 0;
        foreach ($result as $row) {
            if (!$this->checkSpMin($row)) {
                break;
            }
            if ($row['invited']) {
                continue;
            }
            if ($row['gender'] == 'F') {
                $W+=1;
            }
        }
        if ($W < ceil($N / 2)) { // not enough girls (the code assumes reverse is never true)
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
                    $spareGirls+=1;
                }
                if ($spareGirls >= $W) {
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
                    $spareAll+=1;
                }
                if ($spareAll >= $N) {
                    break;
                }
            }
        } else {
            $NK = floor($N / 2);
            $NH = ceil($N / 2);
            $spareK = $spareH = 0;
            foreach ($result as $row) {
                if ($row['invited']) {
                    continue;
                }
                if (!$this->checkSpMin($row)) {
                    break;
                }
                if ($row['gender'] == 'M' && $spareK < $NK) {
                    $row['spare'] = self::RULE4M2;
                    $spareK+=1;
                } elseif ($row['gender'] == 'F' && $spareH < $NH) {
                    $row['spare'] = self::RULE4F2;
                    $spareH+=1;
                }
                if ($spareH + $spareK >= $N) {
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

    /**
     * @param $data
     * @return float|int
     */
    private function findP($data) {
        $Z = $this->parameters['par_z'];
        $P = ceil(($Z - self::RESERVE_1) / self::CAT_COUNT) + 1;

        $Y = 0;
        do {
            $Y = 0;
            $P -= 1;
            foreach ($data as $row) {
                if ($this->inviting($row, $P) && $this->checkInvMin($row)) {
                    $Y += 1;
                }
            }
        } while ($Y > $Z - self::RESERVE_1);
        return $P;
    }

    /**
     * @param $row
     * @param $P
     * @return bool
     */
    private function inviting($row, $P) {
        return $row['category'] == 4 ? ($row['cat_rank'] <= $P - self::P_4) : ($row['cat_rank'] <= $P);
    }

    /**
     * @param $row
     * @return bool
     */
    private function checkInvMin($row) {
        return $row['points'] >= $this->parameters['min_z'];
    }

    /**
     * @param $row
     * @return bool
     */
    private function checkSpMin($row) {
        return $row['points'] >= $this->parameters['min_n'];
    }

}
