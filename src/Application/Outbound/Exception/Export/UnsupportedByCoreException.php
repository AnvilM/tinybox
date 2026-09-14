<?php

declare(strict_types=1);

namespace App\Application\Outbound\Exception\Export;

use App\Application\Outbound\DTO\Export\CoreType;
use App\Domain\Shared\Exception\CoreException;

/**
 * Thrown by the registry when no exporter supports the given node
 * for the requested core (e.g. RealitySecurityVO for sing-box).
 */
final class UnsupportedByCoreException extends CoreException
{
    public function __construct(
        private readonly string   $nodeClass,
        private readonly CoreType $core,
    )
    {
        parent::__construct(sprintf(
            'Node "%s" is not supported by "%s" core.',
            $this->nodeClass,
            $this->core->value,
        ));
    }

    public static function forNode(object $node, CoreType $core): self
    {
        return new self($node::class, $core);
    }

    /**
     * Class name of the node for which no exporter was found.
     *
     * @return class-string
     */
    public function getNodeClass(): string
    {
        return $this->nodeClass;
    }

    public function getCore(): CoreType
    {
        return $this->core;
    }
}
