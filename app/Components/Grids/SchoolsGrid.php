<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;

class SchoolsGrid extends EntityGrid
{

    public function __construct(Container $container)
    {
        parent::__construct($container, SchoolService::class, [], []);
    }

    protected function getData(): IDataSource
    {
        $schools = $this->service->getTable();
        $dataSource = new SearchableDataSource($schools);
        $dataSource->setFilterCallback(function (Selection $table, array $value) {
            $tokens = preg_split('/\s+/', $value['term']);
            foreach ($tokens as $token) {
                $table->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        return $dataSource;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumn('name', _('Name'));
        $this->addColumn('city', _('City'))->setRenderer(fn(SchoolModel $school): string => $school->address->city);
        $this->addColumn('active', _('Active?'))->setRenderer(
            fn(SchoolModel $row): Html => Html::el('span')
                ->addAttributes(['class' => ('badge ' . ($row->active ? 'bg-success' : 'bg-danger'))])
                ->addText(($row->active))
        );

        $this->addORMLink('school.edit');
        $this->addORMLink('school.detail');
    }
}
