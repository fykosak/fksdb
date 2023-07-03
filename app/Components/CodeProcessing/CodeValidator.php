<?php

declare(strict_types=1);

namespace FKSDB\Components\CodeProcessing;

use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

class CodeValidator
{
    /**
     * @throws ForbiddenRequestException
     */
    public static function checkCode(Container $container, string $code, string $saltKey = 'default'): string
    {
        [$id, $checkSum] = explode('-', $code);
        $salt = $container->getParameters()['salt'][$saltKey];
        if (crc32($id . $salt) !== +$checkSum) {
            throw new ForbiddenRequestException(_('Wrong checksum'));
        }
        return $id;
    }
}
