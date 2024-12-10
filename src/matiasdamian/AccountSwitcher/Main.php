<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher;

use jojoe77777\FormAPI\FormAPI;
use matiasdamian\AccountSwitcher\account\AccountManager;
use matiasdamian\AccountSwitcher\command\AccountCommand;
use matiasdamian\AccountSwitcher\config\Config;
use matiasdamian\AccountSwitcher\listener\EventListener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
	/** @var AccountManager|null */
	private ?AccountManager $accountManager = null;
	
	/** @var Config|null */
	private ?Config $pluginConfiguration = null;
	
	/**
	 * Get the AccountManager instance.
	 *
	 * @return AccountManager|null
	 */
	public function getAccountManager(): ?AccountManager{
		return $this->accountManager;
	}
	
	/**
	 * Retrieves the plugin configuration instance.
	 *
	 * @return Config|null Returns the PluginConfiguration instance if loaded, or null if not.
	 */
	public function getConfiguration(): ?Config{
		return $this->pluginConfiguration;
	}
	
	/**
	 * @return void
	 */
	public function onEnable(): void{
		if(!class_exists(FormAPI::class)){
			$this->getLogger()->error("FormAPI not found. Disabling plugin...");
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getServer()->getCommandMap()->register("altswitcher", new AccountCommand($this));
		
		$this->pluginConfiguration = new Config($this);
		$this->accountManager = new AccountManager($this);
		
		$this->getLogger()->info("Plugin enabled.");
		
		
	}
	
	/**
	 * @return void
	 */
	public function onDisable(): void{
		$this->getLogger()->info("Plugin disabled.");
		
		if($this->accountManager !== null){
			$this->accountManager->save();
		}
	}
}