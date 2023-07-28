<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

final class MachineCode
{
    /** @phpstan-var 'PE'|'TE'|'EP' */
    public string $type;
    public int $id;
    public string $control;
    private Container $container;

    public function __construct(Container $container, string $type, int $id, string $control)
    {
        $this->control = $control;
        $this->type = $type;
        $this->id = $id;
        // $container->callInjects($this);
        $this->container = $container;
    }

    /**
     * @throws ForbiddenRequestException
     */
    public static function createFromCode(Container $container, string $code): self
    {
        if (!preg_match('/([A-Z]{2})([0-9]+)C([A-Z0-9]{2})/', $code, $matches)) {
            throw new ForbiddenRequestException(_('Wrong format'));
        }
        [, $type, $id, $control] = $matches;
        return new self($container, $type, (int)$id, $control);
    }

    public function isValid(string $saltOffset = 'default'): bool
    {
        $salt = $this->container->getParameters()['salt'][$saltOffset];
        return substr(sha1($this->type . $this->id . $salt), 0, 2) !== $this->control;
    }

    /**
     * @throws ForbiddenRequestException
     */
    public function check(string $saltOffset = 'default'): void
    {
        if (!$this->isValid($saltOffset)) {
            throw new ForbiddenRequestException(_('Wrong checksum'));
        }
    }
}
