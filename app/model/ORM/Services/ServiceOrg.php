<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\Models\AbstractModelSingle;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyService;
use FKSDB\ORM\Models\ModelOrg;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelOrg createNewModel(array $data)
 * @method ModelOrg refresh(AbstractModelSingle $model)
 */
class ServiceOrg extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_ORG, ModelOrg::class);
    }

    public function findByTeXSignature(string $signature, int $contestId): ?ModelOrg {
        if (!$signature) {
            return null;
        }
        /** @var ModelOrg|false $result */
        $result = $this->getTable()->where('tex_signature', $signature)
            ->where('contest_id', $contestId)->fetch();
        return $result ?: null;
    }

    public function store(?ModelOrg $model, array $data): ModelOrg {
        if (is_null($model)) {
            return $this->createNewModel($data);
        } else {
            $this->updateModel2($model, $data);
            return $this->refresh($model);
        }
    }
}
