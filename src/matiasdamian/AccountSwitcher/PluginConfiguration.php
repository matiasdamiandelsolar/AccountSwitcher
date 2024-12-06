<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher;

use pocketmine\utils\Config;
use pocketmine\utils\ConfigLoadException;

class PluginConfiguration{
	/** @var Main */
	public readonly Main $plugin;
	/** @var Config */
	private readonly Config $config;
	
	/** @var bool */
	private bool $transferOnSwitch;
	/** @var string */
	private string $serverIp;
	/*+ @var int */
	private int $serverPort;
	/** @var bool */
	private bool $allowUngroup;
	/** @var int */
	private int $maxGroupSize;
	/** @var bool */
	private bool $banAltAccounts;
	
	/**
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->config = $plugin->getConfig();
		$this->loadConfigValues();
	}
	
	/**
	 * Load and validate configuration values.
	 */
	private function loadConfigValues(): void{
		$this->transferOnSwitch = (bool) $this->getConfigValue("account-switch.transfer-on-switch");
		
		try{
			$this->serverIp = (string)$this->getConfigValue("account-switch.server-ip");
			$this->validateServerIp();
			
			$this->serverPort = (int)$this->getConfigValue("account-switch.server-port", 19132);
			$this->validateServerPort();
		}catch(ConfigLoadException $e){
			$this->plugin->getLogger()->error($e->getMessage());
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
		}
		
		$this->allowUngroup = (bool) $this->getConfigValue("groups.allow-ungroup");
		$this->maxGroupSize = (int) $this->getConfigValue("groups.max-group-size");
		
		$this->banAltAccounts = (bool) $this->getConfigValue("ban-alt-accounts");
	}
	
	/**
	 * Get a configuration value with a default.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private function getConfigValue(string $key, $default = null) : mixed{
		return $this->config->getNested($key, $default);
	}
	
	/**
	 * Validate and return the server IP address from the configuration.
	 */
	private function validateServerIp() : void{
		if($this->serverIp === ""){
			throw new ConfigLoadException("Missing 'server-ip' in configuration");
		}
		
		if(!filter_var($this->serverIp, FILTER_VALIDATE_IP)){
			throw new ConfigLoadException("'server-ip' must be a valid IP address or hostname");
		}
		
	}
	
	/**
	 * Validate and return the server port from the configuration.
	 */
	private function validateServerPort(): void{
		if($this->serverPort < 0 || $this->serverPort > 65535){
			throw new ConfigLoadException("'server-port' must be a valid port number");
		}
	}
	
	
	/**
	 * @return array
	 */
	public function asArray(): array{
		return $this->config->getAll();
	}
	
	/**
	 * Retrieves the server IP address from the configuration.
	 *
	 * @return string The server IP address.
	 */
	public function getServerIp(): string{
		return $this->serverIp;
	}
	
	/**
	 * Retrieves the server port from the configuration.
	 *
	 * @return int The server port.
	 */
	public function getServerPort(): int{
		return $this->serverPort;
	}
	
	/**
	 * Checks if account transfer on switch is enabled.
	 *
	 * @return bool True if transfer on switch is enabled, false otherwise.
	 */
	public function isTransferOnSwitch(): bool{
		return $this->transferOnSwitch;
	}
	
	/**
	 * Checks if ungrouping of accounts is allowed.
	 *
	 * @return bool True if ungrouping is allowed, false otherwise.
	 */
	public function isAllowUngroup(): bool{
		return $this->allowUngroup;
	}
	
	/**
	 * Retrieves the maximum group size for accounts.
	 *
	 * @return int The maximum number of accounts per group.
	 */
	public function getMaxGroupSize(): int{
		return $this->maxGroupSize;
	}
	
	/**
	 * Checks if banning alts is enabled.
	 * @return bool
	 */
	public function isBanAltAccounts(): bool{
		return $this->banAltAccounts;
	}
	
}