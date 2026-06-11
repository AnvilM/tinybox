<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Entity;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use App\Domain\Subscription\VO\SubscriptionTypeVO;
use App\Domain\Subscription\VO\SubscriptionURLVO;

final class ConfigSubscription extends Subscription
{

    private NonEmptyStringVO $config;

    public function __construct(NonEmptyStringVO $name, SubscriptionURLVO $url, NonEmptyStringVO $config)
    {
        parent::__construct($name, $url);

        $this->config = $config;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getNameString(),
            'url' => $this->getUrl(),
            'type' => SubscriptionTypeVO::Config->value,
            'config' => $this->config->getValue()
        ];
    }


    /**
     * Get subscription config string
     *
     * @return string Subscription config as string
     */
    public function getConfigString(): string
    {
        return $this->config->getValue();
    }
}