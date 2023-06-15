<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker;

use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;

use function get_debug_type;
use function is_int;

final class Main extends PluginBase {
  
  private Config $storage;

	protected function onLoad(): void {
		$this->storage = new Config($this->getDataFolder() . "worlds.yml");

		foreach ($this->storage->getAll() as $worldTime) {
			if (!is_int($worldTime)) {
				 $this->getLogger()->warning("Invalid world time value $worldTime, expected int, got " . get_debug_type($worldTime));
				 $this->getServer()->getPluginManager()->disablePlugin($this);
			}
		}
	}

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event): void {
			foreach ($event->getPackets() as $packet) {
			    if (!($packet instanceof SetTimePacket)) {
			        continue;
			    }
			    foreach ($event->getTargets() as $target) {
			        $player = $target->getPlayer();
			        
			        if ($player === null) {
			            continue;
			        }
			        $world = $player->getWorld();
			        
			        if (!$world->stopTime) {
			             $this->removeBlockedWorldTime($world);
			             continue;
			        }
			        $packet->time = $this->newBlockedWorldTime($world);
			    }
			}
		}, EventPriority::HIGHEST, $this);
		
		$this->getServer()->getPluginManager()->registerEvent(WorldLoadEvent::class, function(WorldLoadEvent $event): void {
			$world = $event->getWorld();

			if (!$world->stopTime && $this->hasBlockedWorldTime($world)) {
				 $world->setTime($this->getBlockedWorldTime($world));
				 $world->stopTime();
			}
		}, EventPriority::NORMAL, $this);
	}

	private function getBlockedWorldTime(World $world): int {
		/** @var int $worldTime */
		$worldTime = $this->storage->get($world->getFolderName(), $world->getTime());
		return $worldTime;
	}
	
	private function hasBlockedWorldTime(World $world): bool {
	    return $this->storage->exists($world->getFolderName());
	}
	
	private function newBlockedWorldTime(World $world): int {
	    if (!$this->hasBlockedWorldTime($world)) {
			 $this->storage->set($world->getFolderName(), $world->getTime());
			 $this->storage->save();
		}
		return $this->getBlockedWorldTime($world);
	}

	private function removeBlockedWorldTime(World $world): void {
		if ($this->hasBlockedWorldTime($world)) {
			$this->storage->remove($world->getFolderName());
			$this->storage->save();
		}
	}
}
