<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\command\subcommand;

use pocketmine\player\Player;

use matiasdamian\AccountSwitcher\command\AccountCommand;
use matiasdamian\AccountSwitcher\account\Account;
use matiasdamian\AccountSwitcher\account\AccountGroup;
use matiasdamian\AccountSwitcher\Main;
use matiasdamian\AccountSwitcher\task\TransferTask;

use matiasdamian\AccountSwitcher\command\subcommand\MainCommandHandler;

use matiasdamian\LangManager\LangManager;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class ManageSubcommand{
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
	 * Allows the player to group accounts together.
	 * @param Player $player
	 * @param array $args
	 * @return bool
	 */
	public function execute(Player $player, array $args): bool{
		$group = $this->getPlugin()->getAccountManager()->getAccountGroup($player->getName());
		/** @var Account[] */
		$accounts = $group->exempt($player->getName());
		
		if(count($accounts) >= 1){
			if(isset($args[1]) && $group->isInGroup($account = $args[1])){
				$this->handleAccountManagementForm($player, $account, $group);
				return true;
			}
		}
		
		if(count($accounts) > 0){
			$this->handleAccountSelectionForm($player, $accounts);
			return true;
		}
		
		$this->command->sendMainForm($player);
		return true;
	}
	
	/**
	 * @param Player $player
	 * @param string $account
	 * @param AccountGroup $group
	 * @return void
	 */
	private function handleAccountManagementForm(Player $player, string $account, AccountGroup $group): void{
		$form = new SimpleForm(function(Player $player, ?string $action) use ($account, $group){
			$group = $this->getPlugin()->getAccountManager()->getAccountGroup($account);
			if(!$group->isInGroup($player->getName())){
				return;
			}
			
			$action ??= "";
			switch($action){
				case "switch":
					$this->switchActiveUsername($player, $account);
					break;
				case "ungroup":
					$this->ungroupAccount($player, $account, $group);
					break;
				default:
					$this->command->sendMainForm($player);
			}
		});
		
		$form->setTitle(LangManager::translate("altswitcher-title", $player));
		$form->setContent(LangManager::translate("altswitcher-managing", $player, $account));
		
		$form->addButton(LangManager::translate("altswitcher-switch", $player), -1, "", MainCommandHandler::SUBCOMMAND_SWITCH);
		if($this->getPlugin()->getConfiguration()->isAllowUngroup()){
			$form->addButton(LangManager::translate("altswitcher-ungroup", $player), -1, "", MainCommandHandler::SUBCOMMAND_UNGROUP);
		}
		
		$form->sendToPlayer($player);
	}
	
	/**
	 * @param Player $player
	 * @param array $accounts
	 * @return void
	 */
	private function handleAccountSelectionForm(Player $player, array $accounts): void{
		$form = new SimpleForm(function(Player $player, ?string $account){
			if(is_string($account)){
				$this->handleAccountManage($player, [MainCommandHandler::SUBCOMMAND_MANAGE, $account]);
				return;
			}
			$this->command->sendMainForm($player);
		});
		
		$form->setTitle(LangManager::translate("altswitcher-title", $player));
		$form->setContent(LangManager::translate("altswitcher-manage-choose", $player));
		
		foreach($accounts as $account){
			$form->addButton(LangManager::translate("altswitcher-account", $player, $account->getUsername()), -1, "", $account->getUsername());
		}
		
		$player->sendForm($form);
	}
	
	/**
	 * @param Player $player
	 * @param string $account
	 * @return void
	 */
	private function switchActiveUsername(Player $player, string $account): void{
		$this->getPlugin()->getAccountManager()->setActiveUsername($player->getUniqueId()->toString(), $account);
		
		if($this->getPlugin()->getConfiguration()->isTransferOnSwitch()){
			LangManager::send("altswitcher-transfer", $player, $account);
			$this->getPlugin()->getScheduler()->scheduleDelayedTask(new TransferTask($this->getPlugin()->getConfiguration(), $player), 60);
		}else{
			LangManager::send("altswitcher-login", $player, $account);
		}
	}
	
	/**
	 * @param Player $player
	 * @param string $account
	 * @param AccountGroup $group
	 * @return void
	 */
	private function ungroupAccount(Player $player, string $account, AccountGroup $group): void{
		if(!$this->getPlugin()->getConfiguration()->isAllowUngroup()){
			LangManager::send("altswitcher-cannot-ungroup", $player);
			return;
		}
		
		$groupB = $this->getPlugin()->getAccountManager()->getAccountGroup($account);
		if($groupB instanceof AccountGroup && $groupB === $group){
			$group->ungroupAccount($account);
			$this->getPlugin()->getAccountManager()->removeGroupRequest($account);
			$this->getPlugin()->getAccountManager()->removeActiveUsername($account);
			LangManager::send("altswitcher-ungrouped", $player, $account);
		}
	}
	
}