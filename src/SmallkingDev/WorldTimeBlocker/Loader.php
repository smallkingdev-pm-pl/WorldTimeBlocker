<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker;

use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\ConfigLoadException;
use SmallkingDev\WorldTimeBlocker\command\BlockWorldTimeCommand;
use SmallkingDev\WorldTimeBlocker\world\WorldManager;

final class Loader extends PluginBase {

    private const COMMAND_FALLBACK_PREFIX = "WorldTimeBlocker";

    private Config $config;
    private WorldManager $worldManager;

    protected function onLoad(): void {
        $this->checkVirions();
        $this->prepareConfiguration();
        $this->worldManager = new WorldManager($this->getDataFolder());
    }

    protected function onEnable(): void {
        $this->registerDefaultCommand();
        $this->registerInterceptor();
    }

    private function checkVirions(): void {
        $virionNameNotFound = match (true) {
            !trait_exists(MarshalTrait::class) => "libMarshal",
            !class_exists(SimplePacketHandler::class) => "SimplePacketHandler",
            default => null
        };

        if (is_string($virionNameNotFound)) {
            $this->getLogger()->error("Virion \'$virionNameNotFound' not found. Please download WorldTimeBlocker from Poggit-CI.");
            throw new DisablePluginException();
        }
    }

    private function prepareConfiguration(): void {
        try {
            $this->config = Config::unmarshal($this->getConfig()->getAll());
        } catch (GeneralMarshalException | UnmarshalException | ConfigLoadException $exception) {
            $this->getLogger()->error($exception->getMessage());
            throw new DisablePluginException();
        }
    }

    private function registerDefaultCommand(): void {
        if (($permission = $this->config->command->permission) !== null) {
            $permissionManager = PermissionManager::getInstance();
            $permissionManager->addPermission(new Permission($permission));

            $root = $permissionManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
            assert($root !== null, "Default permission root not registered");

            $root->addChild($permission, true);
        }
        $this->getServer()->getCommandMap()->register(self::COMMAND_FALLBACK_PREFIX, new BlockWorldTimeCommand($this, $this->config));
    }

    private function registerInterceptor(): void {
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
