<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class ConfigSubscription extends Subscription
{

    private string $config;

    public function __construct(NonEmptyStringVO $name, SubscriptionURLVO $url, string $config)
    {
        parent::__construct($name, $url);

        $this->config = $config;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getNameString(),
            'url' => $this->getUrl(),
            'type' => 'config',
            'config' => $this->config
        ];
    }


    /**
     * Get subscription config
     *
     * @return string Subscription config
     */
    public function getConfig(): string
    {
        return $this->config;
    }
}