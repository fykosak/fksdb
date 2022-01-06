<?php

declare(strict_types=1);

namespace NiftyGrid;

use Nette,
    DibiFluent;
use NiftyGrid\DataSource\IDataSource;

/**
 * DibiFluent datasource for Nifty's grid.
 *
 * <code>
 * $db = new DibiConnection($dbConfig);
 * $fluent = $db->select('id, name, surname')->from('employee')->where('is_active = %b', TRUE);
 * $dataSource = new DibiFluentDataSource($fluent, 'id');
 * </code>
 *
 * <code>
 * $dataSource = new DibiFluentDataSource(dibi::select('*')->from('employee'), 'id');
 * </code>
 *
 * @author  Miloslav Hůla
 * @version 1.2
 * @licence LGPL
 * @see     https://github.com/Niftyx/NiftyGrid
 */
class DibiFluentDataSource implements IDataSource {

    use Nette\SmartObject;

    /** @var DibiFluent */
    private $fluent;

    /** @var string  Primary key column name */
    private $pKeyColumn;

    /** @var int  LIMIT clause value */
    private $limit;

    /** @var int  OFFSET clause value */
    private $offset;

    /**
     * @param DibiFluent
     * @param string  Primary key column name
     */
    public function __construct(DibiFluent $fluent, $pKeyColumn) {
        $this->fluent = clone $fluent;
        $this->pKeyColumn = $pKeyColumn;
    }

    /* --- NiftyGrid\IDataSource implementation ----------------------------- */

    public function getData(): array {
        return $this->fluent->getConnection()->query('%SQL %lmt %ofs', (string)$this->fluent, $this->limit, $this->offset)->fetchAssoc($this->pKeyColumn);
    }

    public function getPrimaryKey(): ?string {
        return $this->pKeyColumn;
    }

    public function getCount(string $column = '*'): int {
        // @see http://forum.dibiphp.com/cs/792-velky-problem-s-dibidatasource-a-mysql

        $fluent = clone $this->fluent;
        $fluent->removeClause('SELECT')->removeClause('ORDER BY');

        $modifiers = \DibiFluent::$modifiers;
        \DibiFluent::$modifiers['SELECT'] = '%sql';
        $fluent->select(['COUNT(%n) AS [count]', $column]);
        \DibiFluent::$modifiers = $modifiers;

        if (strpos((string)$fluent, 'GROUP BY') === false) {
            return $fluent->fetchSingle();
        }

        try {
            return $fluent->execute()->count();
        } catch (\DibiNotSupportedException $e) {
        }

        $count = 0;
        foreach ($fluent as $row) {
            $count += 1;
        }

        return $count;
    }

    public function orderData(?string $by, ?string $way): void {
        $this->fluent->orderBy([$by => $way]);
    }

    public function limitData(int $limit, ?int $offset = null): void {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function filterData(array $filters): void {
        static $typeToModifier = [
            FilterCondition::NUMERIC => '%f',
            FilterCondition::DATE => '%d',
        ];

        $where = [];
        foreach ($filters as $filter) {
            $cond = [];

            // Column
            if (isset($filter['columnFunction'])) {
                $cond[] = $filter['columnFunction'] . '(';
            }

            $cond[] = '%n';
            $cond[] = $filter['column'];

            if (isset($filter['columnFunction'])) {
                $cond[] = ')';
            }

            // Operator
            $cond[] = trim(strtoupper(str_replace('?', '', $filter['cond'])));

            // Value
            if (isset($filter['valueFunction'])) {
                $cond[] = $filter['valueFunction'] . '(';
            }

            $cond[] = isset($typeToModifier[$filter['datatype']]) ? $typeToModifier[$filter['datatype']] : '%s';
            $cond[] = $filter['value'];

            if (isset($filter['valueFunction'])) {
                $cond[] = ')';
            }

            if ($filter['type'] === FilterCondition::WHERE) {
                $where[] = $cond;
            } else {
                trigger_error("Unknown filter type '$filter[type]'.", E_USER_NOTICE);
            }
        }

        if (count($where)) {
            $this->fluent->where($where);
        }
    }

}
