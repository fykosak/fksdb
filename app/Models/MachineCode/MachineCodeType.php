<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\Application\BadRequestException;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 */
enum MachineCodeType: string
{
    case Person = 'PE';
    case Team = 'TE';

    /**
     * @phpstan-return class-string<Service<TSupportedModel>>
     */
    public function getServiceClassName(): string
    {
        /** @phpstan-ignore-next-line */
        return match ($this) {
            self::Person => PersonService::class,
            self::Team => TeamService2::class,
        };
    }

    /**
     * @throws BadRequestException
     * @phpstan-param TSupportedModel $model
     */
    public static function fromModel(Model $model): self
    {
        if ($model instanceof PersonModel) {
            return self::Person;
        } elseif ($model instanceof TeamModel2) {
            return self::Team;
        }
        throw new BadRequestException(_('Wrong type of code.')); //@phpstan-ignore-line
    }
}
