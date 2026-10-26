<?php

declare(strict_types=1);

namespace App\Commands\Shared\Options\Pipeline;

use Iva\Input\Input;

/**
 * Reads the order of the --override / --urltest / --detour flags (-u, -d) from the command line.
 *
 * Iva returns the values of every option separately, but keeps the invocation log:
 * entries in the order the user typed them. Every occurrence of an option is labeled
 * '--<long name>' there, whatever form was used (--urltest a, --urltest=a, -u a).
 *
 * Stateless, safe to share.
 */
final readonly class PipelineFlagOrder
{
    /**
     * @return list<PipelineKind> One element per occurrence of --override / --urltest / --detour (-u, -d),
     *                            in the order of the command line
     */
    public function read(Input $input): array
    {
        $kindByLabel = [];

        foreach (PipelineKind::cases() as $kind) {
            $kindByLabel['--' . $kind->long()] = $kind;
        }

        $order = [];

        foreach ($input->invocation()->options()->labels() as $label) {
            $kind = $kindByLabel[$label] ?? null;

            if ($kind !== null) {
                $order[] = $kind;
            }
        }

        return $order;
    }
}
