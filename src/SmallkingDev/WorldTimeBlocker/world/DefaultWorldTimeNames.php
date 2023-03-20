<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\world;

final class DefaultWorldTimeNames {

    public const KNOWN_NAMES = [
        self::DAY,
        self::NIGHT,
        self::MIDNIGHT,
        self::NOON,
        self::SUNSET,
        self::SUNRISE
    ];

    public const DAY = "day";
    public const NIGHT = "night";
    public const MIDNIGHT = "midnight";
    public const NOON = "noon";
    public const SUNSET = "sunset";
    public const SUNRISE = "sunrise";
}
