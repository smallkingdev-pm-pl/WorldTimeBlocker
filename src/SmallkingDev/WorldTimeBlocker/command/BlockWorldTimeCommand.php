<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use SmallkingDev\WorldTimeBlocker\config\CommandConfig;
use SmallkingDev\WorldTimeBlocker\config\MessageConfig;
use SmallkingDev\WorldTimeBlocker\Loader;
use SmallkingDev\WorldTimeBlocker\utils\StringToWorldTimeParser;

/**
 * @method Loader getOwningPlugin()
 */
final class BlockWorldTimeCommand extends Command implements PluginOwned {
    use PluginOwnedTrait;

    public function __construct(Loader $owningPlugin, private CommandConfig $commandConfig, private MessageConfig $messageConfig) {
        parent::__construct($this->commandConfig->name, $this->commandConfig->description, sprintf($this->commandConfig->usage, $this->commandConfig->name), $this->commandConfig->aliases);
        $this->setPermission($this->commandConfig->permission);

        if (($permissionMessage = $this->messageConfig->noPermission) !== null) {
            $this->setPermissionMessage($permissionMessage);
        }
        $this->owningPlugin = $owningPlugin;
    }

    /**
     * @param array<int, string> $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!($sender instanceof Player) || !$this->testPermission($sender)) {
            return;
        }
        if (empty($args)) {
            $sender->sendMessage(sprintf($this->commandConfig->usage, $commandLabel));
            return;
        }
        $time = StringToWorldTimeParser::parse($worldTimeName = array_shift($args));

        if ($time === null) {
            $sender->sendMessage(sprintf($this->messageConfig->invalidTime, $worldTimeName));
            return;
        }
        $this->getOwningPlugin()->getWorldManager()->setWorldTime($sender->getWorld()->getFolderName(), $time);
        $sender->getWorld()->setTime($time);
        $sender->sendMessage(sprintf($this->messageConfig->timeSet, $worldTimeName));
    }
}
