<?php

declare(strict_types=1);

namespace App\Core\Services\SingBoxService\SaveSingBoxConfig;

use App\Core\Collections\SingBoxConfig\SingBoxConfigMap;
use App\Core\Exceptions\ApplicationException;
use Application\Config\SingBoxConfig\SingBoxConfig;
use JsonException;

final readonly class SaveSingBoxConfig
{
    /**
     * @param SingBoxConfigMap $configs
     * @return void
     * @throws ApplicationException|JsonException
     */
    public function saveSingBoxConfig(SingBoxConfigMap $configs): void
    {
        foreach ($configs as $name => $config) {
            file_put_contents(
                SingBoxConfig::singBoxConfigSaveDirectoryPath() . "/SaveSingBoxConfig.php",
                json_encode($config, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            );
        }
    }
}