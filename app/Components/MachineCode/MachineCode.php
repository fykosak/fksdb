<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\InvalidStateException;

/**
 * @template TModel of \FKSDB\Models\ORM\Models\PersonModel|\FKSDB\Models\ORM\Models\EventParticipantModel|\FKSDB\Models\ORM\Models\Fyziklani\TeamModel2
 */
final class MachineCode
{
    public const TYPE_PERSON = 'PE';
    public const TYPE_PARTICIPANT = 'EP';
    public const TYPE_TEAM = 'TE';

    public const CIP_ALGO = 'aes-256-cbc-hmac-sha1';
    /** @phpstan-var self::TYPE_* */
    public string $type;
    public int $id;
    /** @phpstan-var TModel  */
    public Model $model;


    /**
     * @phpstan-param self::TYPE_* $type
     * @throws NotFoundException
     */
    private function __construct(Container $container, string $type, int $id)
    {
        $this->type = $type;
        $this->id = $id;
        switch ($type) {
            case self::TYPE_PERSON:
                $className = PersonService::class;
                break;
            case self::TYPE_PARTICIPANT:
                $className = EventParticipantService::class;
                break;
            case self::TYPE_TEAM:
                $className = TeamService2::class;
                break;
            default:
                throw new InvalidStateException();
        }
        $service = $container->getByType($className);
        $model = $service->findByPrimary($id);
        if (!$model) {
            throw new NotFoundException();
        }
        $this->model = $model;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     * @phpstan-return self<Model>
     */
    public static function createFromCode(Container $container, string $code, string $saltOffset): self
    {
        $salt = $container->getParameters()['salt'][$saltOffset];
        $data = openssl_decrypt($code, self::CIP_ALGO, $salt);
        if ($data === false) {
            throw new ForbiddenRequestException(_('Cannot decrypt code'));
        }
        if (!preg_match('/([A-Z]{2})([0-9]+)/', $data, $matches)) {
            throw new ForbiddenRequestException(_('Wrong format'));
        }
        [, $type, $id] = $matches;
        return new self($container, $type, (int)$id);// @phpstan-ignore-line
    }
}
