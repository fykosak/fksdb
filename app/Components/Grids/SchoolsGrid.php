<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\NetteORM\TypedSelection;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends FilterGrid<SchoolModel,array{
 *     term?:string,
 * }>
 */
class SchoolsGrid extends FilterGrid
{
    private SchoolService $service;

    public function injectService(SchoolService $service): void
    {
        $this->service = $service;
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('term')->setHtmlAttribute('placeholder', _('Find'));
    }

    /**
     * @phpstan-return TypedSelection<SchoolModel>
     */
    protected function getModels(): TypedSelection
    {
        $query = $this->service->getTable();
        if (!isset($this->filterParams['term'])) {
            return $query;
        }
        $tokens = preg_split('/\s+/', $this->filterParams['term']);
        foreach ($tokens as $token) { //@phpstan-ignore-line
            $query->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->addColumn(
            new RendererItem($this->container, fn(SchoolModel $model) => $model->name),
            'name'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school): string => $school->address->city
            ),
            'city'
        );
        $this->addColumn(
            new RendererItem(
                $this->container,
                fn(SchoolModel $row): Html => Html::el('span')
                    ->addAttributes(['class' => ('badge ' . ($row->active ? 'bg-success' : 'bg-danger'))])
                    ->addText(($row->active))
            ),
            'active'
        );

        $this->addORMLink('school.edit');
        $this->addORMLink('school.detail');
    }
}
