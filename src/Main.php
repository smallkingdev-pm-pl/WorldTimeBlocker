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

use function is_int;

final class Main extends PluginBase {

	private Config $worldTimeBlockStorage;

	protected function onLoad() : void {
		$this->worldTimeBlockStorage = new Config($this->getDataFolder() . "worlds.yml");

		foreach ($this->worldTimeBlockStorage->getAll() as $worldTime) {
			if (!is_int($worldTime)) {
				 $this->getLogger()->warning("Invalid world time value: " . $worldTime);
				 throw new DisablePluginException();
			}
		}
	}

	protected function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void {
			foreach ($event->getPackets() as $packet) {
				if ($packet instanceof SetTimePacket) {
					foreach ($event->getTargets() as $target) {
						$player = $target->getPlayer();

						if ($player !== null) {
							$world = $player->getWorld();

							if (!$world->stopTime) {
								 $this->removeWorldTimeBlock($world);
								 continue;
						   }
						   $session = Session::get($player);

						   if (!$session->setReceivedTime($packet->time)) {
								$event->cancel();
								continue;
						   }
						   $this->setWorldTimeBlock($world);
						}
					}
				}
			}
		}, EventPriority::HIGHEST, $this);
		$this->getServer()->getPluginManager()->registerEvent(WorldLoadEvent::class, function(WorldLoadEvent $event) : void {
			$world = $event->getWorld();

			if (!$world->stopTime && $this->hasWorldTimeBlock($world)) {
				 $world->setTime($this->getWorldTimeBlock($world));
				 $world->stopTime();
			}
		}, EventPriority::NORMAL, $this);
	}

	private function getWorldTimeBlock(World $world) : int {
		/** @var int $worldTime */
		$worldTime = $this->worldTimeBlockStorage->get($world->getFolderName(), $world->getTime());
		return $worldTime;
	}

	private function hasWorldTimeBlock(World $world) : bool {
		return $this->worldTimeBlockStorage->exists($world->getFolderName());
	}

	private function setWorldTimeBlock(World $world) : void {
		if (!$this->hasWorldTimeBlock($world)) {
			 $this->worldTimeBlockStorage->set($world->getFolderName(), $world->getTime());
			 $this->worldTimeBlockStorage->save();
		}
	}

	private function removeWorldTimeBlock(World $world) : void {
		if ($this->hasWorldTimeBlock($world)) {
			$this->worldTimeBlockStorage->remove($world->getFolderName());
			$this->worldTimeBlockStorage->save();
		}
	}
}
