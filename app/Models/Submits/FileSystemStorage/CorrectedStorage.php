<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits\FileSystemStorage;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\Submits\StorageProcessing;
use FKSDB\Models\Submits\SubmitStorage;
use Nette\InvalidStateException;
use Nette\Utils\Finder;

class CorrectedStorage implements SubmitStorage
{
    /** Characters delimiting name and metadata in filename. */
    public const DELIMITER = '__';

    /** @var string  Absolute path to (existing) directory of the storage. */
    private string $root;

    /**
     * Sprintf string for arguments (in order): contestName, year, series, label
     * @var string
     */
    private string $directoryMask;

    public function __construct(string $root, string $directoryMask)
    {
        $this->root = $root;
        $this->directoryMask = $directoryMask;
    }

    /**
     * @throws NotImplementedException
     */
    public function addProcessing(StorageProcessing $processing): void
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function beginTransaction(): void
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function commit(): void
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function rollback(): void
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function storeFile(string $filename, SubmitModel $submit): void
    {
        throw new NotImplementedException();
    }

    public function retrieveFile(SubmitModel $submit, int $type = self::TYPE_PROCESSED): ?string
    {
        $dir = $this->root . DIRECTORY_SEPARATOR . $this->createDirname($submit);

        try {
            $it = Finder::findFiles('*' . self::DELIMITER . $submit->submit_id . '*')->in($dir);
            /** @phpstan-var \SplFileInfo[] $files */
            $files = iterator_to_array($it, false);
        } catch (\UnexpectedValueException $exception) {
            return null;
        }

        if (count($files) == 0) {
            return null;
        } elseif (count($files) > 1) {
            throw new InvalidStateException(
                sprintf(_('Ambiguity in file database for submit #%d.'), $submit->submit_id)
            );
        } else {
            $file = array_pop($files);
            return $file->getRealPath();
        }
    }

    /**
     * Checks whether there exists valid file for the submit.
     */
    public function fileExists(SubmitModel $submit): bool
    {
        return (bool)$this->retrieveFile($submit);
    }

    /**
     * @throws NotImplementedException
     */
    public function deleteFile(SubmitModel $submit): void
    {
        throw new NotImplementedException();
    }

    /**
     * @return string  directory part of the path relative to root, w/out trailing slash
     */
    private function createDirname(SubmitModel $submit): string
    {
        $task = $submit->task;
        return sprintf(
            $this->directoryMask,
            $task->contest->getContestSymbol(),
            $task->year,
            $task->series,
            $task->webalizeLabel()
        );
    }
}
