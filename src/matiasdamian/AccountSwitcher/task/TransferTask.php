<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\task;

use matiasdamian\AccountSwitcher\config\Config;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;

class TransferTask extends Task{
	/** @var Config  */
	private Config $configuration;
	/** @var Player  */
	private Player $player;
	
	/**
	 * @param Config $configuration
	 * @param Player $player
	 */
	public function __construct(Config $configuration, Player $player){
		$this->configuration = $configuration;
		$this->player = $player;
	}
	
	/**
	 * Called when the task is run.
	 * @return void
	 */
	public function onRun() : void{
		if($this->player->isOnline()){
			
			$serverIp = $this->configuration->getServerIp();
			$serverPort = $this->configuration->getServerPort();
			
			$this->player->transfer($serverIp, $serverPort);
		}
	}
	
}