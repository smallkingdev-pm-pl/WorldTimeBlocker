<?php

declare(strict_types=1);

namespace SmallkingDev\WorldTimeBlocker\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use SmallkingDev\WorldTimeBlocker\Config;
use SmallkingDev\WorldTimeBlocker\Loader;
use SmallkingDev\WorldTimeBlocker\utils\StringToWorldTimeParser;
use SmallkingDev\WorldTimeBlocker\world\DefaultWorldTimeNames;

use function sprintf;
use function array_shift;
use function strtolower;
use function in_array;

final class BlockWorldTimeCommand extends Command implements PluginOwned {

    public function __construct(private Loader $plugin, private Config $config) {
        parent::__construct($this->config->command->name, $this->config->command->description, sprintf($this->config->command->usage, $this->config->command->name), $this->config->command->aliases);
        $this->setPermission($this->config->command->permission);

        if (($permissionMessage = $this->config->messages->noPermission) !== null) {
            $this->setPermissionMessage($permissionMessage);
        }
    }

    /**
     * @param array<int, string> $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!($sender instanceof Player) || !$this->testPermission($sender)) {
            return;
        }
        if (empty($args)) {
            $sender->sendMessage(sprintf($this->config->command->usage, $commandLabel));
            return;
        }
        $worldTimeName = strtolower(array_shift($args));

        if (!in_array($worldTimeName, DefaultWorldTimeNames::KNOWN_NAMES, true)) {
            $sender->sendMessage(sprintf($this->config->messages->invalidTime, $worldTimeName));
            return;
        }
        $time = StringToWorldTimeParser::parse($worldTimeName);

        if ($this->getOwningPlugin()->getWorldManager()->setWorldTime($sender->getWorld(), $time)) {
            $sender->sendMessage(sprintf($this->config->messages->timeSet, $worldTimeName));
            return;
        }
        $sender->sendMessage(sprintf($this->config->messages->timeAlreadySet, $worldTimeName));
    }

    public function getOwningPlugin(): Loader {
        return $this->plugin;
    }
}
