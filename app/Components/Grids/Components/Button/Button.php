<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Control;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * @template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
abstract class Button extends BaseItem
{
    /** @var callable(TModel):array{string,array<string,scalar>} */
    private $linkCallback;
    /** @var (callable(TModel,int):bool)|null */
    private $showCallback;
    private ?string $buttonClassName;

    /**
     * @phpstan-param callable(TModel):array{string,array<string,scalar>} $linkCallback
     * @phpstan-param (callable(TModel,int):bool)|null $showCallback
     */
    public function __construct(
        Container $container,
        Title $title,
        callable $linkCallback,
        ?string $buttonClassName = null,
        ?callable $showCallback = null
    ) {
        parent::__construct($container, $title);
        $this->linkCallback = $linkCallback;
        $this->buttonClassName = $buttonClassName;
        $this->showCallback = $showCallback;
    }

    /**
     * @param TModel $model
     * @throws InvalidLinkException
     */
    public function render(Model $model, int $userPermission): void
    {
        [$destination, $params] = ($this->linkCallback)($model);
        $html = Html::el('a');
        if (!isset($this->showCallback) || ($this->showCallback)($model, $userPermission)) {
            $html->addAttributes([
                'href' => $this->getLinkControl()->link($destination, $params),
                'class' => $this->buttonClassName ?? 'btn btn-sm me-1 btn-outline-secondary',
            ]);
            $html->setHtml(
                isset($this->title) ? $this->title->toHtml() : $this->getLinkControl()->link($destination, $params)
            );
        }
        $this->renderHtml($html);
    }

    abstract protected function getLinkControl(): Control;
}
