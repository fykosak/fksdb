<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Database\Conventions\AmbiguousReferenceKeyException;
use Nette\Database\Explorer;

/**
 * @phpstan-extends Test<PersonModel>
 */
final class EmptyPerson extends Test
{
    private Explorer $explorer;
    /** @phpstan-var array<string,string> */
    private static array $refTables;

    public function inject(Explorer $explorer): void
    {
        $this->explorer = $explorer;
    }

    /**
     * @phpstan-return  array<string,string>
     */
    private function getReferencingTables(): array
    {
        if (!isset(self::$refTables)) {
            self::$refTables = [];
            foreach ($this->explorer->getConnection()->getDriver()->getTables() as $otherTable) {
                try {
                    /**
                     * @var string $table
                     * @var string $refColumn
                     */
                    [$table, $refColumn] = $this->explorer->getConventions()->getHasManyReference(
                        DbNames::TAB_PERSON,
                        $otherTable['name']
                    );
                    if (
                        in_array(
                            $table,
                            ['dakos_person', DbNames::TAB_PERSON_INFO, DbNames::TAB_LOGIN, DbNames::TAB_POST_CONTACT]
                        )
                    ) {
                        continue;
                    }
                    if ($table) {
                        self::$refTables[$table] = $refColumn;
                    }
                } catch (AmbiguousReferenceKeyException $exception) {
                    /* empty */
                }
            }
        }

        return self::$refTables;
    }

    public function run(TestLogger $logger, Model $model): void
    {
        foreach ($this->getReferencingTables() as $table => $column) {
            if ($model->related($table, $column)->fetch()) {
                return;
            }
        }
        $logger->log(new TestMessage($this->formatId($model), _('Person is empty!'), Message::LVL_WARNING));
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Empty person'));
    }

    public function getId(): string
    {
        return 'EmptyPerson';
    }
}
