<?php

declare(strict_types=1);

namespace App\Domain\Scheme\VO\ShadowsocksScheme\Userinfo;

enum ShadowsocksMethod: string
{
    case BLAKE3_AES_128_GCM_2022 = '2022-blake3-aes-128-gcm';
    case BLAKE3_AES_256_GCM_2022 = '2022-blake3-aes-256-gcm';
    case BLAKE3_CHACHA20_POLY1305_2022 = '2022-blake3-chacha20-poly1305';

    case NONE = 'none';

    case AES_128_GCM = 'aes-128-gcm';
    case AES_192_GCM = 'aes-192-gcm';
    case AES_256_GCM = 'aes-256-gcm';

    case CHACHA20_IETF_POLY1305 = 'chacha20-ietf-poly1305';
    case XCHACHA20_IETF_POLY1305 = 'xchacha20-ietf-poly1305';

    case AES_128_CTR = 'aes-128-ctr';
    case AES_192_CTR = 'aes-192-ctr';
    case AES_256_CTR = 'aes-256-ctr';

    case AES_128_CFB = 'aes-128-cfb';
    case AES_192_CFB = 'aes-192-cfb';
    case AES_256_CFB = 'aes-256-cfb';

    case RC4_MD5 = 'rc4-md5';

    case CHACHA20_IETF = 'chacha20-ietf';
    case XCHACHA20 = 'xchacha20';
}