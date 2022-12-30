<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\SchoolModel;
use FKSDB\Models\ORM\Services\SchoolService;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;
use Nette\Forms\Form;
use Nette\Utils\Html;

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

    protected function getModels(): Selection
    {
        $query = $this->service->getTable();
        if (!isset($this->filterParams) || !isset($this->filterParams['term'])) {
            return $query;
        }
        $tokens = preg_split('/\s+/', $this->filterParams['term']);
        foreach ($tokens as $token) {
            $query->where('name_full LIKE CONCAT(\'%\', ? , \'%\')', $token);
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(): void
    {
        $this->tableRow->addComponent(
            new RendererItem($this->container, fn(SchoolModel $model) => $model->name, new Title(null, _('Name'))),
            'name'
        );
        $this->tableRow->addComponent(
            new RendererItem(
                $this->container,
                fn(SchoolModel $school): string => $school->address->city,
                new Title(null, _('City'))
            ),
            'city'
        );
        $this->tableRow->addComponent(
            new RendererItem(
                $this->container,
                fn(SchoolModel $row): Html => Html::el('span')
                    ->addAttributes(['class' => ('badge ' . ($row->active ? 'bg-success' : 'bg-danger'))])
                    ->addText(($row->active)),
                new Title(null, _('Active?'))
            ),
            'active'
        );

        $this->addORMLink('school.edit');
        $this->addORMLink('school.detail');
    }
}
