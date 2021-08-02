<?php

namespace FKSDB\Models\Submits;

use FKSDB\Models\ORM\Models\ModelSubmit;

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
     * @return void
     * @throws StorageException for unsuccessful commit
     */
    public function commit(): void;

    public function rollback(): void;

    public function addProcessing(StorageProcessing $processing): void;

    /**
     * File is renamed/moved to own purposes.
     *
     * @param string $filename
     * @param ModelSubmit $submit
     * @return void
     */
    public function storeFile(string $filename, ModelSubmit $submit): void;

    /**
     *
     * @param ModelSubmit $submit
     * @param int $type
     * @return string filename with absolute path
     */
    public function retrieveFile(ModelSubmit $submit, int $type = self::TYPE_PROCESSED): ?string;

    public function fileExists(ModelSubmit $submit): bool;

    public function deleteFile(ModelSubmit $submit): void;
}
