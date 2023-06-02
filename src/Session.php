<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker;

use pocketmine\player\Player;

final class Session {

	/** @phpstan-var \WeakMap<Player, Session> */
	private static \WeakMap $sessions;

	public static function get(Player $player) : Session {
		if(!isset(self::$sessions)){
			/** @phpstan-var \WeakMap<Player, Session> $map */
			$map = new \WeakMap();
			self::$sessions = $map;
		}
		return self::$sessions[$player] ??= new Session();
	}

	public function __construct(
		private int $receivedTime = -1
	) {}

	public function setReceivedTime(int $receivedTime) : bool {
		if ($this->receivedTime !== $receivedTime) {
			$this->receivedTime = $receivedTime;
			return true;
		}
		return false;
	}
}
