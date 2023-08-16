<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Models\Exceptions\GoneException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @template TModel of \Fykosak\NetteORM\Model
 */
abstract class BaseItem extends BaseComponent
{
    public ?Title $title;

    public function __construct(Container $container, ?Title $title = null)
    {
        parent::__construct($container);
        $this->title = $title;
    }

    /**
     * @throws GoneException
     */
    protected function getTemplatePath(): string
    {
        throw new GoneException();
    }

    /**
     * @param TModel $model
     * @note do not call from parent
     * @throws GoneException
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(
            $this->getTemplatePath(),
            [
                'model' => $model,
                'title' => $this->title,
                'userPermission' => $userPermission,
            ]
        );
    }

    /**
     * @param Html|string $html
     */
    final public function renderHtml($html): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'html.latte', ['html' => $html,]);
    }
}
