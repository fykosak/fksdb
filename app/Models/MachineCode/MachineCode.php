<?php

declare(strict_types=1);

namespace FKSDB\Models\MachineCode;

use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * @phpstan-type TSupportedModel = (
 *     \FKSDB\Models\ORM\Models\PersonModel
 *     |\FKSDB\Models\ORM\Models\Fyziklani\TeamModel2)
 */
final class MachineCode
{
    private const CIP_ALGO = 'aes-256-cbc';

    /**
     * @phpstan-param TSupportedModel $model
     * @throws MachineCodeException
     * @throws NotImplementedException
     * @throws BadRequestException
     */
    public static function createModelHash(Model $model, string $salt): string
    {
        $type = MachineCodeType::fromModel($model);
        $modelCode = $type->value . $model->getPrimary();
        $code = openssl_encrypt($modelCode, self::CIP_ALGO, $salt);
        if ($code === false) {
            throw new MachineCodeException(_('Cannot encrypt code'));
        }
        return $code;
    }

    /**
     * @phpstan-return TSupportedModel $model
     * @throws MachineCodeException
     */
    public static function parseModelHash(Container $container, string $code, string $salt): Model
    {
        $data = self::parseStringHash($code, $salt);
        if (!preg_match('/([A-Z]{2})([0-9]+)/', $data, $matches)) {
            throw new MachineCodeException(_('Wrong format'));
        }
        [, $type, $id] = $matches;
        $type = MachineCodeType::from($type);
        /** @var Service<TSupportedModel> $service */
        $service = $container->getByType($type->getServiceClassName());
        $model = $service->findByPrimary($id);
        if (!$model) {
            throw new MachineCodeException(_('Model not found'));
        }
        return $model;
    }

    /**
     * @throws MachineCodeException
     * @throws BadRequestException
     */
    public static function createStringHash(string $data, string $salt): string
    {
        $code = openssl_encrypt($data, self::CIP_ALGO, $salt);
        if ($code === false) {
            throw new MachineCodeException(_('Cannot encrypt code'));
        }
        return $code;
    }

    /**
     * @throws MachineCodeException
     */
    public static function parseStringHash(string $code, string $salt): string
    {
        $data = openssl_decrypt($code, self::CIP_ALGO, $salt);
        if ($data === false) {
            throw new MachineCodeException(_('Cannot decrypt code'));
        }
        return $data;
    }
}
