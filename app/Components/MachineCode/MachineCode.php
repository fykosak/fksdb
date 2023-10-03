<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

final class MachineCode
{
    public const CIP_ALGO = 'aes-256-cbc-hmac-sha1';
    /** @phpstan-var 'PE'|'TE'|'EP' */
    public string $type;
    public int $id;
    public string $control;

    /**
     * @phpstan-param 'PE'|'TE'|'EP' $type
     */
    private function __construct(string $type, int $id)
    {
        $this->type = $type;
        $this->id = $id;
        // $container->callInjects($this);
    }

    /**
     * @throws ForbiddenRequestException
     */
    public static function createFromCode(Container $container, string $code, string $saltOffset): self
    {
        $salt = $container->getParameters()['salt'][$saltOffset];
        $data = openssl_decrypt($code, self::CIP_ALGO, $salt);
        Debugger::log($data);
        if (!preg_match('/([A-Z]{2})([0-9]+)/', $data, $matches)) {
            throw new ForbiddenRequestException(_('Wrong format'));
        }
        [, $type, $id] = $matches;
        return new self($type, (int)$id);// @phpstan-ignore-line
    }
}
