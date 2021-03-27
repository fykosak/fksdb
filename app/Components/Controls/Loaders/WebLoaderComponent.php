<?php

namespace FKSDB\Components\Controls\Loaders;

use FKSDB\Components\Controls\BaseComponent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class WebLoaderComponent extends BaseComponent {

    public const FILENAME = 'file';
    public const ATTRIBUTES = 'attr';
    public const UNTAGGED = '__untagged';

    private array $files = [];

    private array $inlines = [];

    public function addFile(string $file, array $attributes = []): void {
        $hash = $file . join(':', $attributes);
        $this->files[$hash] = [
            self::FILENAME => $file,
            self::ATTRIBUTES => $attributes,
        ];
        $this->redrawControl();
    }

    public function removeFile(string $file, array $attributes = []): void {
        $hash = $file . join(':', $attributes);
        unset($this->files[$hash]);
        $this->redrawControl();
    }

    public function addInline(string $inline, string $tag = self::UNTAGGED): void {
        $this->inlines[$tag] = $inline;
        $this->redrawControl();
    }

    public function removeInline(string $tag): void {
        if ($tag != self::UNTAGGED) {
            unset($this->inlines[$tag]);
        }
        $this->redrawControl();
    }

    final public function render(...$args): void {
        $files = [];
        if (count($args) == 1 && is_array($args[0])) {
            foreach ($args[0] as $file => $attributes) {
                $files[] = [
                    self::FILENAME => $file,
                    self::ATTRIBUTES => $attributes,
                ];
            }
        } else {
            foreach ($args as $arg) {
                $files[] = [
                    self::FILENAME => $arg,
                    self::ATTRIBUTES => [],
                ];
            }
        }

        $this->template->files = array_merge($files, $this->getFiles());
        $this->template->render($this->getDir() . 'layout.files.latte');
    }

    final public function renderInline(): void {
        $this->template->inlines = $this->getInLines();
        $this->template->render($this->getDir() . 'layout.inlines.latte');
    }

    public static function isRelative(string $file): bool {
        return !preg_match('@https?://|/@Ai', $file);
    }

    protected function getFiles(): array {
        return $this->files;
    }

    protected function getInLines(): array {
        return $this->inlines;
    }

    abstract protected function getDir(): string;
}
