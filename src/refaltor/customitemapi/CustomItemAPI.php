<?php

/*
 *    _______           _______ _________ _______  _______ __________________ _______  _______  _______  _______ _________
 *   (  ____ \|\     /|(  ____ \\__   __/(  ___  )(       )\__   __/\__   __/(  ____ \(       )(  ___  )(  ____ )\__   __/
 *   | (    \/| )   ( || (    \/   ) (   | (   ) || () () |   ) (      ) (   | (    \/| () () || (   ) || (    )|   ) (
 *   | |      | |   | || (_____    | |   | |   | || || || |   | |      | |   | (__    | || || || (___) || (____)|   | |
 *   | |      | |   | |(_____  )   | |   | |   | || |(_)| |   | |      | |   |  __)   | |(_)| ||  ___  ||  _____)   | |
 *   | |      | |   | |      ) |   | |   | |   | || |   | |   | |      | |   | (      | |   | || (   ) || (         | |
 *   | (____/\| (___) |/\____) |   | |   | (___) || )   ( |___) (___   | |   | (____/\| )   ( || )   ( || )      ___) (___
 *   (_______/(_______)\_______)   )_(   (_______)|/     \|\_______/   )_(   (_______/|/     \||/     \||/       \_______/
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   By: refaltor
 *   Discord: Refaltor#6969
 */


declare(strict_types=1);

namespace refaltor\customitemapi;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use refaltor\customitemapi\items\BaseItem;
use refaltor\customitemapi\items\CustomArmor;
use refaltor\customitemapi\items\CustomAxe;
use refaltor\customitemapi\items\CustomFood;
use refaltor\customitemapi\items\CustomHoe;
use refaltor\customitemapi\items\CustomPickaxe;
use refaltor\customitemapi\items\CustomPotion;
use refaltor\customitemapi\items\CustomSword;
use refaltor\customitemapi\managers\ItemManager;
use refaltor\customitemapi\events\listeners\PacketListeners;
use refaltor\customitemapi\events\listeners\PlayerListeners;
use refaltor\customitemapi\traits\DevUtils;
use refaltor\customitemapi\traits\UtilsTrait;

class CustomItemAPI extends PluginBase
{
    const LAST_VERSION = "3.2.3";

    use UtilsTrait;
    use DevUtils;

    private ItemManager $manager;
    private static self $instance;

    protected function onLoad(): void
    {
        $this->getServer()->getLogger()->debug("[CustomItemAPI] Logs //: CustomItemAPI starting plugin...");
        $this->saveDefaultConfig();
        $this->manager = new ItemManager($this);
        $this->loadConfigurationFiles();
        self::$instance = $this;
    }

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListeners($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PacketListeners($this), $this);

        $this->getAPI()->start();
        $version = $this->getDescription()->getVersion();
        $versionNow = $this->getConfig()->get('version', '1.0.0');
        if ($version != $versionNow) {
            if ($versionNow != self::LAST_VERSION) {
                $this->getServer()->getLogger()->error("[CustomItemAPI] Error //: CustomItemAPI has outdated, 3.2.3 is now");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            } else {
                $configAll = $this->getConfig()->getAll();
                rename($this->getDataFolder() . 'config.yml', $this->getDataFolder() . 'last_config.yml');
                $this->saveDefaultConfig();
                if (isset($configAll['version'])) {
                    $configAll['version'] = '3.2.3';
                    $this->getConfig()->setAll($configAll);
                    $this->getConfig()->save();
                }
                $this->getServer()->getLogger()->error("[CustomItemAPI] Update Auto //: CustomItemAPI config.yml has update.");
            }
        }
        $this->getServer()->getLogger()->debug("[CustomItemAPI] Logs //: CustomItemAPI has started.");
    }

    protected function onDisable(): void
    {
        $this->getServer()->getLogger()->debug("[CustomItemAPI] Logs //: CustomItemAPI has disable.");
    }

    public static function getInstance(): self { return self::$instance; }

    public function getAPI(): ItemManager {
        return $this->manager;
    }
}
