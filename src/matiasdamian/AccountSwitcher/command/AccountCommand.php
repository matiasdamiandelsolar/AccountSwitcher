<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

use matiasdamian\AccountSwitcher\Main;
use matiasdamian\AccountSwitcher\command\subcommand\GroupSubcommand;
use matiasdamian\AccountSwitcher\command\subcommand\ManageSubcommand;

use matiasdamian\LangManager\LangManager;
use jojoe77777\FormAPI\SimpleForm;

class AccountCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;
	
	/** @var AccountCommand */
	private readonly AccountCommand $accountCommand;
	/** @var ManageSubcommand  */
	private readonly ManageSubcommand $manageSubcommand;
	/** @var GroupSubcommand  */
	private readonly GroupSubcommand $groupSubcommand;
	
	public const SUBCOMMAND_MANAGE = "manage";
	public const SUBCOMMAND_GROUP = "group";
	
	/**
	 * @param Main $plugin The main plugin instance.
	 */
	public function __construct(Main $plugin){
		$this->owningPlugin = $plugin;
		parent::__construct("account", "Allows you to switch between accounts without logging out of Xbox Live", "/account", ["alt"]);
		$this->setPermission("altswitcher.command");
		$this->setLanguageDefaults();
		
		$this->groupSubcommand = new GroupSubcommand($this);
		$this->manageSubcommand = new ManageSubcommand($this);
	}
	
	public function getOwningPlugin(): Plugin{
		return $this->owningPlugin;
	}
	
	/**
	 * Gets the plugin instance.
	 *./
	 * @return Main The plugin instance.
	 */
	public function getPlugin(): Main{
		return $this->getOwningPlugin();
	}
	
	/**
	 * Sets the default language keys for the plugin.
	 *
	 * @return void
	 */
	private function setLanguageDefaults(): void{
		LangManager::addKey("altswitcher-title", "&lAccount Manager");
		LangManager::addKey("altswitcher-desc", "Grouping allows you to sign in to other accounts without having to log out Xbox Live.");
		LangManager::addKey("altswitcher-description", "Grouping allows you to sign in to other accounts without having to log out Xbox Live.");
		LangManager::addKey("altswitcher-manage", "Manage your accounts");
		LangManager::addKey("altswitcher-group", "Group this account");
		LangManager::addKey("altswitcher-transfer", "&eLogging in to {%0}...");
		LangManager::addKey("altswitcher-login", "&ePlease rejoin to log in as {%0}");
		LangManager::addKey("altswitcher-account", "{%0}");
		LangManager::addKey("altswitcher-ungroup", "Ungroup this account");
		LangManager::addKey("altswitcher-cannot-ungroup", "&cYou cannot ungroup accounts.");
		LangManager::addKey("altswitcher-manage-choose", "Click on the account you want to manage:");
		LangManager::addkey("altswitcher-manage-account", "{%0}");
		LangManager::addKey("altswitcher-managing", "You are managing {%0}");
		LangManager::addKey("altswitcher-switch", "Switch to this account");
		LangManager::addKey("altswitcher-already-grouped", "&cThis account is already grouped.");
		LangManager::addKey("altswitcher-grouped", "&aYou have grouped the accounts! To switch between accounts use /account");
		LangManager::addKey("altswitcher-ungrouped", "&aYou have ungrouped from {%0}");
		LangManager::addKey("altswitcher-no-group-request", "&cYou have not sent a group request to that username");
		LangManager::addKey("altswitcher-group-limit", "&cYou cannot group more than {%0} accounts at this time.");
		LangManager::addKey("altswitcher-cannot-group", "&eTo complete grouping the accounts, {%0} must type the command /account group {%1}");
		LangManager::addKey("altswitcher-other", "&cEnter another user.");
		LangManager::addkey("altswitcher-finish", "&eLog in with {%0} and type /account group {%1} to finish grouping the accounts.");
		LangManager::addKey("altswitcher-disclaimer", "&cDisclaimer: {LINE}Grouping allows you to log in between accounts. This can be dangerous if the account is not owned by you. Would you like to proceed?");
		LangManager::addKey("altswitcher-enter-username", "Enter the account you want to group");
		LangManager::addKey("altswitcher-username", "Username");
	}
	
	public function execute(CommandSender $sender, string $label, array $args): bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "This command can only be used in-game");
			return false;
		}
		$group = $this->getPlugin()->getAccountManager()->getAccountGroup($sender->getName());
		
		if($group === null){
			return false;
		}
		return $this->handleMainCommand($sender, $args);
	}
	
	private function handleMainCommand(Player $player, array $args) : bool{
		$action = $args[0] ?? "";
		return match (strtolower($action)) {
			self::SUBCOMMAND_MANAGE => $this->manageSubcommand->execute($player, $args),
			self::SUBCOMMAND_GROUP => $this->groupSubcommand->execute($player, $args),
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