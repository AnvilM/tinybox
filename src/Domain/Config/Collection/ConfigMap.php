<?php

declare(strict_types=1);

namespace App\Domain\Config\Collection;

use App\Domain\Config\Entity\Config;
use App\Domain\Config\Exception\ConfigAlreadyExistsException;
use App\Domain\Config\Exception\ConfigNotFoundException;
use App\Domain\Config\VO\ConfigNameVO;
use App\Domain\Shared\Exception\Json\UnableToEncodeJsonException;
use JsonException;
use Psl\Collection\MutableMap;
use Psl\Collection\MutableVector;

final readonly class ConfigMap
{
    /**
     * Config map <configName: string, config: Config>
     *
     * @var MutableMap<string, Config>
     */
    private MutableMap $map;

    public function __construct()
    {
        $this->map = new MutableMap([]);
    }

    /**
     * Convert config map to JSON
     *
     * [{'name': 'conf1', 'schemes': ['scheme1', 'scheme2', ...]}, {'name': 'conf2', 'schemes': [...]}, ...]
     *
     * @return string JSON
     *
     * @throws UnableToEncodeJsonException If unable to encode json
     */
    public function toJson(): string
    {
        /**
         * Assert map is not empty
         */
        if ($this->map->isEmpty()) return '[]';


        $array = [];


        /**
         * Mapping map to array
         */
        foreach ($this->map as $config) {
            $array[] = [
                'name' => $config->getName(),
                'schemes' => $config->getSchemesIds()->getSchemesIdsArray()
            ];
        }


        /**
         * Try to encode JSON
         */
        try {
            return json_encode(
                $array,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new UnableToEncodeJsonException();
        }
    }

    /**
     * Get config by name
     *
     * @param ConfigNameVO $name Config name
     *
     * @return Config Config
     *
     * @throws ConfigNotFoundException If config not fund
     */
    public function getByName(ConfigNameVO $name): Config
    {
        /**
         * Get config
         */
        $config = $this->map->get($name->getName());


        /**
         * Assert config isn't null
         */
        if ($config === null) throw new ConfigNotFoundException();


        /**
         * Return config
         */
        return $config;
    }

    /**
     * Get config names
     *
     * @return MutableVector<string> Vector of config names
     */
    public function getConfigNames(): MutableVector
    {
        $configNames = new MutableVector([]);

        foreach ($this->map as $config) {
            $configNames->add($config->getName());
        }

        return $configNames;
    }

    /**
     * Add config to map
     *
     * @param Config $config Config
     *
     * @throws ConfigAlreadyExistsException If config already exists in map
     */
    public function add(Config $config): void
    {
        /**
         * Check if config name already exists
         */
        if ($this->containsConfig($config)) throw new ConfigAlreadyExistsException();


        /**
         * Add config to map
         */
        $this->map->add($config->getName(), $config);
    }

    /**
     * Check config already exists in map
     *
     * @param Config $config Config
     *
     * @return bool Returns true if exists
     */
    public function containsConfig(Config $config): bool
    {
        return $this->map->containsKey($config->getName());
    }
}