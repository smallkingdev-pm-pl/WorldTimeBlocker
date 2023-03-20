<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\config;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

final class MessageConfig {
    use MarshalTrait;

    #[Field("no-permission")]
    public ?string $noPermission = "You do not have permission to use this command";

    #[Field("invalid-time")]
    public string $invalidTime = "Invalid time";

    #[Field("time-set")]
    public string $timeSet = "Successfully set world time to %s";

    #[Field("time-already-set")]
    public string $timeAlreadySet = "World time is already set to %s";
}
