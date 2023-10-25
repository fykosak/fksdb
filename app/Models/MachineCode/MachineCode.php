<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

final class MachineCode
{
    private const CIP_ALGO = 'aes-256-ebc';

    /**
     * @param PersonModel|EventParticipantModel|TeamModel2 $model
     * @throws MachineCodeException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    public static function createHash(Model $model, string $salt): string
    {
        $code = openssl_encrypt(self::createCode($model), self::CIP_ALGO, $salt);
        if ($code === false) {
            throw new MachineCodeException(_('Cannot encrypt code'));
        }
        return $code;
    }

    /**
     * @param PersonModel|EventParticipantModel|TeamModel2 $model
     * @throws MachineCodeException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    public static function createCode(Model $model): string
    {
        $type = MachineCodeType::fromModel($model);
        return $type->value . $model->getPrimary();
    }

    /**
     * Parse code and return model
     * code must be in format AA123456...
     * @return PersonModel|EventParticipantModel|TeamModel2
     * @throws MachineCodeException
     */
    public static function parseCode(Container $container, string $code): Model
    {
        if (!preg_match('/([A-Z]{2})([0-9]+)/', $code, $matches)) {
            throw new MachineCodeException(_('Wrong format'));
        }
        [, $type, $id] = $matches;
        $type = MachineCodeType::from($type);
        /** @var Service<PersonModel|EventParticipantModel|TeamModel2> $service */
        $service = $container->getByType($type->getServiceClassName());
        $model = $service->findByPrimary($id);
        if (!$model) {
            throw new MachineCodeException(_('Model not found'));
        }
        return $model;
    }

    /**
     * @return PersonModel|EventParticipantModel|TeamModel2
     * @throws MachineCodeException
     */
    public static function parseHash(Container $container, string $code, string $salt): Model
    {
        $data = openssl_decrypt($code, self::CIP_ALGO, $salt);
        if ($data === false) {
            throw new MachineCodeException(_('Cannot decrypt code'));
        }
        return self::parseCode($container, $data);
    }

    /**
     * @throws MachineCodeException
     */
    public static function getSaltForEvent(EventModel $event): string
    {
        switch ($event->event_type_id) {
            case 2:
            case 14:
                $salt = $event->getParameter('hashSalt');
                break;
            default:
                throw new MachineCodeException(_('Not implemented'));
        }
        if (!$salt) {
            throw new MachineCodeException(_('Empty salt'));
        }
        return (string)$salt;
    }
}
