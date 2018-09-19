<?php

namespace FKSDB\model\Fyziklani;

/**
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class TaskCodePreprocessor {


    public static function checkControlNumber($taskCode) {
        if (strlen($taskCode) != 9) {
            return false;
        }
        $subCode = str_split(self::getNumLabel($taskCode));
        $c = 3 * ($subCode[0] + $subCode[3] + $subCode[6]) + 7 * ($subCode[1] + $subCode[4] + $subCode[7]) + ($subCode[2] + $subCode[5] + $subCode[8]);
        return $c % 10 == 0;
    }

    public static function extractTeamId($numLabel) {
        return (int)substr($numLabel, 0, 6);
    }

    public static function extractTaskLabel($teamTaskLabel) {
        return (string)substr($teamTaskLabel, 6, 2);
    }

    public static function getNumLabel($teamTaskLabel) {
        return str_replace(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], [1, 2, 3, 4, 5, 6, 7, 8], $teamTaskLabel);
    }

}
