<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 23.12.2016
 * Time: 2:20
 */

namespace FKSDB\model\Fyziklani;


use OrgModule\FyziklaniPresenter;

class TaskCodePreprocessor {

    public function checkControlNumber($taskCode) {
        $subCode = str_split($this->getNumLabel($taskCode));
        $c = 3 * ($subCode[0] + $subCode[3] + $subCode[6]) + 7 * ($subCode[1] + $subCode[4] + $subCode[7]) + ($subCode[2] + $subCode[5] + $subCode[8]);
        return $c % 10 == 0;
    }

    public function extractTeamID($numLabel) {
        return (int)substr($numLabel, 0, 6);
    }

    public function extractTaskLabel($teamTaskLabel) {
        return (string)substr($teamTaskLabel, 6, 2);
    }

    public function getNumLabel($teamTaskLabel) {
        return str_replace(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], [1, 2, 3, 4, 5, 6, 7, 8], $teamTaskLabel);
    }

}