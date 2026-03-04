<?php

declare(strict_types=1);

namespace App\Core\Shared\ReporterEvent\Events\Shared\IO\File;

use App\Core\Shared\ReporterEvent\ReporterEvent;
use App\Core\Shared\VO\ReporterEvent\DebugMessagesVO;
use App\Core\Shared\VO\ReporterEvent\TypeVO;

final readonly class FileSavedSuccessfullyReporterEvent extends ReporterEvent
{
    public function __construct(string $message, string $filePath, ?string $fileContent = null)
    {
        parent::__construct(
            $message,
            TypeVO::Success,
            DebugMessagesVO::create($fileContent ? [$filePath, $fileContent] : [$filePath]),
        );
    }
}