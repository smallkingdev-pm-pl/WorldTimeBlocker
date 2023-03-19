<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\config;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

final class CommandConfig {
    use MarshalTrait;

    #[Field]
    public string $name = "blockTime";

    #[Field]
    public string $description = "Block the time in a world";

    #[Field]
    public string $usage = "/%s <time>";

    /**
     * @var array<int, string> $aliases
     */
    #[Field]
    public array $aliases = ["bt"];

    #[Field]
    public ?string $permission = "blockTime.command";
}
