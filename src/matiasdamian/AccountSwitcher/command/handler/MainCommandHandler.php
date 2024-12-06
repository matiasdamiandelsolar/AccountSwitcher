<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\command\handler;

use pocketmine\player\Player;

use matiasdamian\AccountSwitcher\Main;
use matiasdamian\AccountSwitcher\command\AccountCommand;
use matiasdamian\LangManager\LangManager;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class MainCommandHandler{
	/** @var AccountCommand */
	private readonly AccountCommand $command;
	
	/** @var GroupSubcommandHandler  */
	private readonly GroupSubcommandHandler $groupSubcommandHandler;
	/** @var ManageSubcommandHandler  */
	private readonly ManageSubcommandHandler $manageSubcommandHandler;
	
	public const SUBCOMMAND_MANAGE = "manage";
	public const SUBCOMMAND_GROUP = "group";
	
	public function __construct(AccountCommand $command
	){
		$this->command = $command;
		$this->groupSubcommandHandler = new GroupSubcommandHandler($command);
		$this->manageSubcommandHandler = new ManageSubcommandHandler($command);
	}
	
	/**
	 * @return Main
	 */
	private function getPlugin(): Main{
		return $this->command->getPlugin();
	}
	
	/**
	 * Handles the main command.
	 * @param Player $player
	 * @param array $args
	 * @return bool
	 */
	public function execute(Player $player, array $args): bool{
		return $this->handleMainCommand($player, $args);
	}
	
	private function handleMainCommand(Player $player, array $args): bool{
		$action = $args[0] ?? "";
		return match (strtolower($action)) {
			self::SUBCOMMAND_MANAGE => $this->manageSubcommandHandler->execute($player, $args),
			self::SUBCOMMAND_GROUP => $this->groupSubcommandHandler->execute($player, $args),
			default => $this->sendMainForm($player, $args)
		};
	}
	
	/**
	 * Sends the main form to the player.
	 *
	 * @param Player $player
	 * @param array $args
	 * @return bool
	 */
	public function sendMainForm(Player $player, array $args = []): bool{
		$form = new SimpleForm(function(Player $player, ?string $action){
			if(is_string($action)){
				$this->handleMainCommand($player, [$action]);
			}
		});
		$group = $this->getPlugin()->getAccountManager()->getAccountGroup($player->getName());
		
		$form->setTitle(LangManager::translate("altswitcher-title", $player));
		$form->setContent(LangManager::translate("altswitcher-desc", $player));
		
		if(count($group->getAccounts()) > 1){
			$form->addButton(LangManager::translate("altswitcher-manage", $player), -1, "", self::SUBCOMMAND_MANAGE);
			return true;
		}
		$form->addButton(LangManager::translate("altswitcher-group", $player), -1, "", self::SUBCOMMAND_GROUP);
		
		$player->sendForm($form);
		return true;
	}
	
}