<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\config;

enum ConfigKeys: string{
	case TRANSFER_ON_SWITCH = "account-switch.transfer-on-switch";
	case SERVER_IP = "account-switch.server-ip";
	case SERVER_PORT = "account-switch.server-port";
	case BAN_ALT_ACCOUNTS = "account-switch.ban-alts";
	case ALLOW_UNGROUP = "groups.allow-ungroup";
	case MAX_GROUP_SIZE = "groups.max-group-size";
	
	/**
	 * @return string
	 */
	public function getConfigKey() : string{
		return $this->value;
	}
}