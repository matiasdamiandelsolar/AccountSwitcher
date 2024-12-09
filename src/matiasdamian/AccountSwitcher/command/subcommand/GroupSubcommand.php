<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\command\subcommand;

use matiasdamian\AccountSwitcher\command\AccountCommand;
use matiasdamian\AccountSwitcher\Main;
use matiasdamian\LangManager\LangManager;
use pocketmine\player\Player;
use pocketmine\player\IPlayer;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class GroupSubcommand{
	/** @var AccountCommand */
	private readonly AccountCommand $command;
	
	public function __construct(AccountCommand $command
	){
		$this->command = $command;
	}
	
	/**
	 * @return Main
	 */
	private function getPlugin(): Main{
		return $this->command->getPlugin();
	}
	
	/**
	 * Group subcommand handler.
	 * @param Player $player
	 * @param array $args
	 * @return bool
	 */
	public function execute(Player $player, array $args): bool{
		if(isset($args[1])){
			return $this->handleGroupRequest($player, $args[1]);
		}
		return $this->handleGroupForm($player);
	}
	
	/**
	 * Allows the player to group accounts together.
	 * @param Player $player
	 * @param string $receiver
	 * @return bool
	 */
	private function handleGroupRequest(Player $player, string $receiver): bool{
		$receiver = strtolower($receiver);
		$accountManager = $this->getPlugin()->getAccountManager();
		
		$requester = $accountManager->hasGroupRequest($receiver);
		$requesterGroup = $accountManager->getAccountGroup(strval($requester));
		
		if($requester === false){
			return true;
		}
		if($requesterGroup !== null && strcasecmp($player->getName(), $requester) === 0){
			$accountManager->removeGroupRequest($requester);
			
			$receiverGroup = $accountManager->getAccountGroup($receiver);
			
			if($requesterGroup->isInGroup($receiver)){
				LangManager::send("altswitcher-already-grouped", $player);
				return true;
			}
			
			// Group the accounts
			$requesterGroup->groupAccount($receiver, $player->getXuid());
			$receiverGroup->ungroupAccount($receiver);
			
			foreach($receiverGroup->getAccounts() as $account){
				$requesterGroup->groupAccount(
					$account->getUsername(),
					$account->getXuid(),
					$account->getLastLogin()
				);
			}
			
			LangManager::send("altswitcher-grouped", $player);
			return true;
		}
		
		LangManager::send("altswitcher-cannot-group", $player, $requester, $player->getName());
		return true;
	}
	
	/**
	 * Handle group form.
	 * @param Player $player
	 * @return void
	 */
	private function handleGroupForm(Player $player): bool{
		$accountManager = $this->getPlugin()->getAccountManager();
		
		$maxGroupSize = $this->getPlugin()->getConfiguration()->getMaxGroupSize();
		$currentGroupSize = count($accountManager->getAccountGroup($player->getName())->getAccounts());
		
		if($currentGroupSize >= $maxGroupSize){
			LangManager::send("altswitcher-group-limit", $player, $maxGroupSize - 1);
			return true;
		}
		
		$form = new CustomForm(function(Player $player, $data){
			if(!isset($data[2]) || !is_string($username = $data[2])){
				// If no valid data is provided, return to the main form
				$this->command->sendMainForm($player);
				return;
			}
			$receiver = $player->getServer()->getOfflinePlayer($username);
			
			if($receiver instanceof IPlayer){
				if(strcasecmp($username, $player->getName()) === 0){
					LangManager::send("altswitcher-other", $player);
					return;
				}
				$this->getPlugin()->getAccountManager()->setGroupRequest($player->getName(), $username);
				LangManager::send("altswitcher-finish", $player, $username, $player->getName());
			}
		});
		
		$form->setTitle(LangManager::translate("altswitcher-title", $player));
		$form->addLabel(LangManager::translate("altswitcher-disclaimer", $player));
		$form->addLabel(LangManager::translate("altswitcher-enter-username", $player));
		$form->addInput(LangManager::translate("altswitcher-username", $player));
		
		$player->sendForm($form);
		return true;
	}
	
}