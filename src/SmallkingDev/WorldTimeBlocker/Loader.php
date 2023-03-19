<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker;

use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use SmallkingDev\WorldTimeBlocker\command\BlockWorldTimeCommand;
use SmallkingDev\WorldTimeBlocker\config\CommandConfig;
use SmallkingDev\WorldTimeBlocker\config\MessageConfig;
use SmallkingDev\WorldTimeBlocker\world\WorldManager;

final class Loader extends PluginBase {

    private WorldManager $worldManager;

    protected function onLoad(): void {
        $this->worldManager = new WorldManager($this->getDataFolder());
    }

    protected function onEnable(): void {
        $this->registerDefaultCommand();
        $this->registerSimpleInterceptor();
    }

    private function registerDefaultCommand(): void {
        $commandConfig = CommandConfig::unmarshal($this->getConfig()->get("command", [])); // @phpstan-ignore-line
        $messageConfig = MessageConfig::unmarshal($this->getConfig()->get("messages", [])); // @phpstan-ignore-line

        if (($permission = $commandConfig->permission) !== null) {
            $permissionManager = PermissionManager::getInstance();
            $permissionManager->addPermission(new Permission($permission));

            $root = $permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
            assert($root !== null, "Default permission root not registered");

            $root->addChild($permission, true);
        }
        $this->getServer()->getCommandMap()->register("blockTime", new BlockWorldTimeCommand($this, $commandConfig, $messageConfig));
    }

    private function registerSimpleInterceptor(): void {
        SimplePacketHandler::createInterceptor($this)
            ->interceptOutgoing(function (SetTimePacket $packet, NetworkSession $target): bool {
                $player = $target->getPlayer();

                if ($player !== null) {
                    $world = $player->getWorld();

                    if (($time = $this->worldManager->getWorldTime($world->getFolderName())) !== null) {
                        $packet->time = $time;
                    }
                    return true;
                }
                return false;
            });
    }

    public function getWorldManager(): WorldManager {
        return $this->worldManager;
    }
}
