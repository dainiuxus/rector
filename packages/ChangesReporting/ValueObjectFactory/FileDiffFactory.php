<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Console\Formatter\ConsoleDiffer;
use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\FileSystem\FilePathHelper;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Reporting\FileDiff;
final class FileDiffFactory
{
    /**
     * @var \Rector\Core\Differ\DefaultDiffer
     */
    private $defaultDiffer;
    /**
     * @var \Rector\Core\Console\Formatter\ConsoleDiffer
     */
    private $consoleDiffer;
    /**
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    public function __construct(DefaultDiffer $defaultDiffer, ConsoleDiffer $consoleDiffer, FilePathHelper $filePathHelper)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->consoleDiffer = $consoleDiffer;
        $this->filePathHelper = $filePathHelper;
    }
    public function createFileDiff(File $file, string $oldContent, string $newContent) : FileDiff
    {
        return $this->createFileDiffWithLineChanges($file, $oldContent, $newContent, $file->getRectorWithLineChanges());
    }
    /**
     * @param RectorWithLineChange[] $rectorsWithLineChanges
     */
    public function createFileDiffWithLineChanges(File $file, string $oldContent, string $newContent, array $rectorsWithLineChanges) : FileDiff
    {
        $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
        // always keep the most recent diff
        return new FileDiff($relativeFilePath, $this->defaultDiffer->diff($oldContent, $newContent), $this->consoleDiffer->diff($oldContent, $newContent), $rectorsWithLineChanges);
    }
    public function createTempFileDiff(File $file) : FileDiff
    {
        return $this->createFileDiffWithLineChanges($file, '', '', $file->getRectorWithLineChanges());
    }
}
