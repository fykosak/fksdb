<?php

declare(strict_types=1);

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\SubmitModel;

/**
 * Storage for signle file for each submit. Storage must keep original file
 * which can be modified by processings for later use.
 */
interface SubmitStorage
{
    public const TYPE_ORIGINAL = 0;
    public const TYPE_PROCESSED = 1;

    public function beginTransaction(): void;

    /**
     * @throws StorageException for unsuccessful commit
     */
    public function commit(): void;

    public function rollback(): void;

    public function addProcessing(StorageProcessing $processing): void;

    /**
     * File is renamed/moved to own purposes.
     */
    public function storeFile(string $filename, SubmitModel $submit): void;

    /**
     * @return string filename with absolute path
     */
    public function retrieveFile(SubmitModel $submit, int $type = self::TYPE_PROCESSED): ?string;

    public function fileExists(SubmitModel $submit): bool;

    public function deleteFile(SubmitModel $submit): void;
}
