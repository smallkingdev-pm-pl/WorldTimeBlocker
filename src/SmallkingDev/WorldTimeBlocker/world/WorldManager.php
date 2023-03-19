<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\world;

use pocketmine\Server;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;

final class WorldManager {

    private Config $storage;

    public function __construct(string $path) {
        $this->storage = new Config(Path::join($path, "worlds.json"));
        $this->deleteInvalidWorlds();
    }

    private function deleteInvalidWorlds(): void {
        /** @var array<int, string> $worlds */
        $worlds = array_diff(scandir(Server::getInstance()->getDataPath() . "worlds"), [".", ".."]); // @phpstan-ignore-line

        foreach ($worlds as $worldName) {
            if (!$this->storage->exists($worldName)) {
                $this->storage->remove($worldName);
            }
        }
    }

    public function getWorldTime(string $worldName): ?int {
        return $this->storage->get($worldName, null); // @phpstan-ignore-line
    }

    public function setWorldTime(string $worldName, int $time): void {
        $this->storage->set($worldName, $time);
        $this->storage->save();
    }
}
