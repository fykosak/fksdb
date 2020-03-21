<?php

namespace FKSDB\model\Fyziklani;

/**
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class TaskCodePreprocessor {
    /**
     * @param string $code
     * @return bool
     */
    public static function checkControlNumber(string $code): bool {
        if (strlen($code) != 9) {
            return false;
        }
        $subCode = str_split(self::getNumLabel($code));
        $sum = 3 * ($subCode[0] + $subCode[3] + $subCode[6]) + 7 * ($subCode[1] + $subCode[4] + $subCode[7]) + ($subCode[2] + $subCode[5] + $subCode[8]);
        return $sum % 10 == 0;
    }

    /**
     * @param string $code
     * @return int
     */
    public static function extractTeamId(string $code): int {
        return (int)substr($code, 0, 6);
    }

    /**
     * @param string $code
     * @return string
     */
    public static function extractTaskLabel(string $code): string {
        return (string)substr($code, 6, 2);
    }

    /**
     * @param string $code
     * @return string
     */
    public static function getNumLabel(string $code): string {
        return str_replace(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], [1, 2, 3, 4, 5, 6, 7, 8], $code);
    }

    /**
     * @param string $code
     * @return string
     * @throws TaskCodeException
     */
    public static function createFullCode(string $code): string {
        $length = strlen($code);
        if ($length > 9) {
            throw new TaskCodeException(_('Code is too long'));
        }

        return str_repeat('0', 9 - $length) . strtoupper($code);
    }
}
