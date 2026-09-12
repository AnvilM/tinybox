<?php

declare(strict_types=1);

namespace App\Application\Outbound\Filter\Contract;

use Psl\Collection\VectorInterface;

/**
 * Implemented by a {@see OutboundFilterCriteriaInterface} that supports
 * excepting specific outbounds from *itself only*.
 *
 * This is intentionally different from
 * {@see \App\Application\Outbound\DTO\UseCase\FilterOutbounds\FilterOutboundsDTO::$ignoreOutbounds}
 * (a.k.a. `--exceptOutbound` on the CLI):
 *
 *  - `FilterOutboundsDTO::$ignoreOutbounds` bypasses the *entire* filtering
 *    pipeline for the listed outbounds (they are merged back in after every
 *    criteria has been applied).
 *  - `getExceptOutbounds()` on a single criteria only makes *that one rule*
 *    treat the listed outbounds as satisfying it. The outbound is still
 *    fully subject to every other criteria in the pipeline.
 *
 * Example: `--countryCode NL --countryCodeExcept my-vps` keeps only NL
 * outbounds, but `my-vps` is never dropped by the country filter - it can
 * still be dropped by e.g. `--excludeOutboundType shadowsocks` though.
 *
 * {@see \App\Application\Outbound\Filter\OutboundFilterService} checks for
 * this interface generically, so implementing it is the *only* thing a new
 * criteria needs to do to support per-filter excepting - no Specification or
 * Factory needs to know about it.
 */
interface OutboundFilterCriteriaExceptOutboundsInterface extends OutboundFilterCriteriaInterface
{
    /**
     * @return VectorInterface<string>|null Tags of outbounds excepted from this specific criteria
     */
    public function getExceptOutbounds(): ?VectorInterface;
}
