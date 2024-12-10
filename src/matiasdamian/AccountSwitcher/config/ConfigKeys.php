<?php

declare(strict_types=1);

namespace matiasdamian\AccountSwitcher\config;

interface ConfigKeys{
	
	public const TRANSFER_ON_SWITCH = "account-switch.transfer-on-switch";
	public const SERVER_IP = "account-switch.server-ip";
	public const SERVER_PORT = "account-switch.server-port";
	public const BAN_ALT_ACCOUNTS = "account-switch.ban-alts";
	public const ALLOW_UNGROUP = "groups.allow-ungroup";
	public const MAX_GROUP_SIZE = "groups.max-group-size";
	
}