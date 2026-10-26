<?php

declare(strict_types=1);

namespace App\Application\Outbound\Selection\Property;

use App\Application\Outbound\Selection\Exception\UnknownPropertyException;

/**
 * Central place that knows every selectable property.
 *
 * The constructor lists concrete classes (not `iterable`), so PHP-DI autowires
 * the registry without any manual container definition.
 *
 * To add a new property:
 *  1. Create a class implementing `Property` (Selection/Property).
 *  2. Add it as a typed constructor parameter here and to the map below.
 */
final readonly class PropertyRegistry
{
    /**
     * @var array<string, Property>
     */
    private array $properties;

    public function __construct(
        TypeProperty        $type,
        TagProperty         $tag,
        ServerProperty      $server,
        PortProperty        $port,
        SecurityProperty    $security,
        SniProperty         $sni,
        FingerprintProperty $fingerprint,
        TransportProperty   $transport,
        FlowProperty        $flow,
        MethodProperty      $method,
        PluginProperty      $plugin,
    )
    {
        $this->properties = [
            $type->name() => $type,
            $tag->name() => $tag,
            $server->name() => $server,
            $port->name() => $port,
            $security->name() => $security,
            $sni->name() => $sni,
            $fingerprint->name() => $fingerprint,
            $transport->name() => $transport,
            $flow->name() => $flow,
            $method->name() => $method,
            $plugin->name() => $plugin,
        ];
    }

    /**
     * @throws UnknownPropertyException
     */
    public function get(string $name, string $source, int $position): Property
    {
        return $this->properties[$name]
            ?? throw new UnknownPropertyException($name, $this->names(), $this->suggest($name), $source, $position);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->properties);
    }

    private function suggest(string $name): ?string
    {
        $best = null;
        $bestDistance = 3;

        foreach ($this->names() as $candidate) {
            $distance = levenshtein($name, $candidate);

            if ($distance <= 2 && $distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        return $best;
    }
}
