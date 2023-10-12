<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

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
    public const CIP_ALGO = 'aes-256-cbc-hmac-sha1';

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
    private static function createCode(Model $model): string
    {
        $type = MachineCodeType::tryFromModel($model);
        return $type->value . $model->getPrimary();
    }

    /**
     * @return PersonModel|EventParticipantModel|TeamModel2
     * @throws MachineCodeException
     */
    private static function parseCode(Container $container, string $code): Model
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
        return self::parseCode($container, $code);
    }

    /**
     * @throws NotImplementedException
     */
    public static function getSaltForEvent(EventModel $event): string
    {
        switch ($event->event_type_id) {
            case 2:
            case 14:
                return $event->getParameter('hashSalt');
            default:
                throw new NotImplementedException();
        }
    }
}
