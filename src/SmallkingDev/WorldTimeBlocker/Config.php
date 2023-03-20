<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;
use SmallkingDev\WorldTimeBlocker\config\CommandConfig;
use SmallkingDev\WorldTimeBlocker\config\MessageConfig;

final class Config {
    use MarshalTrait;

    #[Field]
    public CommandConfig $command;

    #[Field]
    public MessageConfig $messages;
}
