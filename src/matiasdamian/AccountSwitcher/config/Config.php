<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\config;

use matiasdamian\AccountSwitcher\Main;
use pocketmine\utils\ConfigLoadException;

class Config{
	/** @var \pocketmine\utils\Config */
	private readonly \pocketmine\utils\Config $config;
	
	/**
	 * @var array<string, mixed>
	 */
	private array $settings = [];
	
	/**
	 * Config constructor.
	 * @param Main $plugin Plugin instance.
	 */
	public function __construct(private readonly Main $plugin){
		$this->config = $plugin->getConfig();
		$this->loadConfigValues();
	}
	
	/**
	 * Loads configuration values from the config file.
	 * Validates specific keys as needed.
	 */
	private function loadConfigValues(): void{
		try{
			$this->settings = [
				ConfigKeys::TRANSFER_ON_SWITCH => (bool)$this->getConfigValue(ConfigKeys::TRANSFER_ON_SWITCH, false),
				ConfigKeys::SERVER_IP => (string)$this->getConfigValue(ConfigKeys::SERVER_IP, ""),
				ConfigKeys::SERVER_PORT => (int)$this->getConfigValue(ConfigKeys::SERVER_PORT, 19132),
				ConfigKeys::ALLOW_UNGROUP => (bool)$this->getConfigValue(ConfigKeys::ALLOW_UNGROUP, false),
				ConfigKeys::MAX_GROUP_SIZE => (int)$this->getConfigValue(ConfigKeys::MAX_GROUP_SIZE, 10),
				ConfigKeys::BAN_ALT_ACCOUNTS => (bool)$this->getConfigValue(ConfigKeys::BAN_ALT_ACCOUNTS, false)
			];
			
			$this->validateServerIp();
			$this->validateServerPort();
		}catch(ConfigLoadException $e){
			$this->plugin->getLogger()->error($e->getMessage());
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
		}
	}
	
	/**
	 * Retrieves a configuration value, returning a default if it doesn't exist.
	 *
	 * @param string $key The key of the configuration value.
	 * @param mixed $default The default value if the key is not found.
	 * @return mixed The configuration value.
	 */
	private function getConfigValue(string $key, mixed $default): mixed{
		return $this->config->getNested($key, $default);
	}
	
	/**
	 * Validates the server IP address.
	 *
	 * @throws ConfigLoadException If the server IP is invalid.
	 */
	private function validateServerIp(): void{
		$serverIp = $this->getServerIp();
		if(empty($serverIp) || !filter_var($serverIp, FILTER_VALIDATE_IP)){
			throw new ConfigLoadException("'" . ConfigKeys::SERVER_IP . "' must be a valid IP address or hostname");
		}
	}
	
	/**
	 * Validates the server port.
	 *
	 * @throws ConfigLoadException If the server port is invalid.
	 */
	private function validateServerPort(): void{
		$serverPort = $this->getServerPort();
		if($serverPort < 1 || $serverPort > 65535){
			throw new ConfigLoadException("'" . ConfigKeys::SERVER_PORT . "' must be a valid port number between 1 and 65535");
		}
	}
	
	/**
	 * Returns all configuration settings as an associative array.
	 *
	 * @return array<string, mixed>
	 */
	public function asArray(): array{
		return $this->config->getAll();
	}
	
	/**
	 * Retrieves a specific setting by its key.
	 *
	 * @param string $key The key of the setting to retrieve.
	 * @return mixed|null The setting value, or null if not found.
	 */
	private function get(string $key): mixed{
		return $this->settings[$key] ?? null;
	}
	
	/**
	 * Retrieves the server IP address.
	 *
	 * @return string The server IP address.
	 */
	public function getServerIp(): string{
		return $this->get(ConfigKeys::SERVER_IP);
	}
	
	/**
	 * Retrieves the server port.
	 *
	 * @return int The server port.
	 */
	public function getServerPort(): int{
		return $this->get(ConfigKeys::SERVER_PORT);
	}
	
	/**
	 * Checks if account transfer on switch is enabled.
	 *
	 * @return bool True if transfer is enabled, false otherwise.
	 */
	public function isTransferOnSwitch(): bool{
		return $this->get(ConfigKeys::TRANSFER_ON_SWITCH);
	}
	
	/**
	 * Checks if ungrouping of accounts is allowed.
	 *
	 * @return bool True if ungrouping is allowed, false otherwise.
	 */
	public function isAllowUngroup(): bool{
		return $this->get(ConfigKeys::ALLOW_UNGROUP);
	}
	
	/**
	 * Retrieves the maximum group size for accounts.
	 *
	 * @return int The maximum number of accounts per group.
	 */
	public function getMaxGroupSize(): int{
		return $this->get(ConfigKeys::MAX_GROUP_SIZE);
	}
	
	/**
	 * Checks if banning alternate accounts is enabled.
	 *
	 * @return bool True if banning is enabled, false otherwise.
	 */
	public function isBanAltAccounts(): bool{
		return $this->get(ConfigKeys::BAN_ALT_ACCOUNTS);
	}
	
}