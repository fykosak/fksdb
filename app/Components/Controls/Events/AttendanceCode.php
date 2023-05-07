<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

class AttendanceCode
{
    /**
     * @throws ForbiddenRequestException
     */
    public static function checkCode(Container $container, string $code): int
    {
        [$id, $checkSum] = explode('-', $code);
        $salt = $container->getParameters()['salt'];
        if (crc32($id . $salt) !== +$checkSum) {
            throw new ForbiddenRequestException(_('Bad checksum'));
        }
        return +$id;
    }
}
