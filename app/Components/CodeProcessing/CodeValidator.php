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
        [$id, $checkSum] = self::parseCode($code);
        $salt = $container->getParameters()['salt'][$saltKey];
        if (substr(sha1($id . $salt), 0, 2) !== $checkSum) {
            throw new ForbiddenRequestException(_('Wrong checksum'));
        }
        return $id;
    }

    public static function bypassCode(string $code): string
    {
        [$id] = self::parseCode($code);
        return $id;
    }

    /**
     * @return string[]
     */
    private static function parseCode(string $code): array
    {
        return explode('-', $code);
    }
}
