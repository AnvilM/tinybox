<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO\ShadowsocksScheme\Userinfo;

use App\Domain\Shared\VO\Shared\NonEmptyStringVO;
use InvalidArgumentException;

final readonly class ShadowsocksUserinfoVO extends NonEmptyStringVO
{
    private ShadowsocksMethod $method;
    private string $password;


    /**
     * @param string|null $userinfo Raw encoded userinfo
     *
     * @throws InvalidArgumentException If userinfo format is invalid or method is empty or password is empty
     */
    public function __construct(?string $userinfo)
    {
        parent::__construct($userinfo);

        [$method, $password] = $this->parse(
            $this->getRawUserinfo()
        );


        $this->method = ShadowsocksMethod::tryFrom($method) ?? throw new InvalidArgumentException("Unsupported method: " . "'" . ($method ?? 'null') . "'");
        $this->password = $password;
    }

    /**
     * Parse raw userinfo string
     *
     * @throws InvalidArgumentException If invalid userinfo format
     */
    private function parse(string $userinfo): array
    {
        /**
         * Check if userinfo is not encoded
         */
        if (str_contains($userinfo, ':')) {
            return $this->explodeUserinfo($userinfo);
        }

        /**
         * Try to decode userinfo
         */
        $decoded = base64_decode($userinfo);


        /**
         * Check if userinfo is valid format
         */
        if ($decoded === false || !str_contains($decoded, ':')) {
            throw new InvalidArgumentException("Invalid userinfo provided");
        }

        return $this->explodeUserinfo($decoded);
    }

    /**
     * Explode decoded userinfo by ':'
     *
     * @throws InvalidArgumentException If method or password is empty
     */
    private function explodeUserinfo(string $userinfo): array
    {
        [$method, $password] = explode(':', $userinfo, 2);

        if ($method === '') {
            throw new InvalidArgumentException('Method cannot be empty');
        }

        if ($password === '') {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        return [$method, $password];
    }


    /**
     * Get raw encoded userinfo
     *
     * @return string Raw encoded userinfo
     */
    public function getRawUserinfo(): string
    {
        return $this->getValue();
    }


    /**
     * Get shadowsocks method
     *
     * @return ShadowsocksMethod Shadowsocks method
     */
    public function getMethod(): ShadowsocksMethod
    {
        return $this->method;
    }


    /**
     * Get password
     *
     * @return string Password
     */
    public function getPassword(): string
    {
        return $this->password;
    }


}