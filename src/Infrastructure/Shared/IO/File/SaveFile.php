<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\IO\File;

use App\Core\Shared\Exception\File\UnableToSaveFileException;
use App\Core\Shared\Ports\IO\File\SaveFilePort;

final readonly class SaveFile implements SaveFilePort
{

    public function save(string $path, string $fileContent): void
    {
        $fileSavingResult = @file_put_contents($path, $fileContent);

        if ($fileSavingResult === false) throw new UnableToSaveFileException();
    }


}