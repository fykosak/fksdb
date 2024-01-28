<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\Application\BadRequestException;
use Nette\InvalidStateException;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
final class MachineCodeType extends FakeStringEnum
{
    // phpcs:disable
    public const Person = 'PE';
    public const Team = 'TE';
    // phpcs:enable

    public static function cases(): array
    {
        return [
            new self(self::Person),
            new self(self::Team),
        ];
    }

    /**
     * @phpstan-return class-string<Service<TSupportedModel>>
     */
    public function getServiceClassName(): string
    {
        switch ($this->value) {
            case self::Person:
                return PersonService::class;// @phpstan-ignore-line
            case self::Team:
                return TeamService2::class;// @phpstan-ignore-line
            default:
                throw new InvalidStateException();
        }
    }

    /**
     * @throws BadRequestException
     * @phpstan-param TSupportedModel $model
     */
    public static function fromModel(Model $model): self
    {
        if ($model instanceof PersonModel) {
            return new self(self::Person);
        } elseif ($model instanceof TeamModel2) {
            return new self(self::Team);
        }
        throw new BadRequestException(_('Wrong type of code.')); //@phpstan-ignore-line
    }
}
