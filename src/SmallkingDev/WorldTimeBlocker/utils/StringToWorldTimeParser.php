<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\utils;

use pocketmine\world\World;
use SmallkingDev\WorldTimeBlocker\world\DefaultWorldTimeNames;

final class StringToWorldTimeParser {

    /**
     * @param DefaultWorldTimeNames::* $worldTimeName
     */
    public static function parse(string $worldTimeName): int {
        return match ($worldTimeName) {
            DefaultWorldTimeNames::DAY => World::TIME_DAY,
            DefaultWorldTimeNames::NIGHT => World::TIME_NIGHT,
            DefaultWorldTimeNames::MIDNIGHT => World::TIME_MIDNIGHT,
            DefaultWorldTimeNames::NOON => World::TIME_NOON,
            DefaultWorldTimeNames::SUNSET => World::TIME_SUNSET,
            DefaultWorldTimeNames::SUNRISE => World::TIME_SUNRISE
        };
    }
}