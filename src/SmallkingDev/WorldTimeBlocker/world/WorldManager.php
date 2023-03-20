<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\world;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;

use function array_diff;
use function scandir;

final class WorldManager {

    private Config $storage;

    public function __construct(string $path) {
        $this->storage = new Config(Path::join($path, "worlds.json"));
        $this->deleteInvalidWorlds();
    }

    private function deleteInvalidWorlds(): void {
        /** @var array<int, string> $worlds */
        $worlds = array_diff(scandir(Server::getInstance()->getDataPath() . "worlds"), [".", ".."]);

        foreach ($worlds as $worldName) {
            if (!$this->storage->exists($worldName)) {
                $this->storage->remove($worldName);
            }
        }
    }

    public function getWorldTime(string $worldName): ?int {
        return $this->storage->get($worldName, null);
    }

    public function setWorldTime(World $world, int $time): bool {
        $worldTime = $this->getWorldTime($world->getFolderName());

        if ($worldTime !== $time) {
            $this->storage->set($world->getFolderName(), $time);
            $this->storage->save();

            $world->setTime($time);
            return true;
        }
        return false;
    }
}
