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


namespace refaltor\customitemapi\events\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\ItemFactory;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomFood;
use refaltor\customitemapi\items\CustomPotion;
use refaltor\customitemapi\traits\OwnedTrait;

class PlayerListeners implements  Listener
{
    use OwnedTrait;

    public function __construct(CustomItemAPI $plugin)
    {
        $this->setPlugin($plugin);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $network = $player->getNetworkSession();
        $network->sendDataPacket($this->getPlugin()->getAPI()->getPacket());
    }

    public function onFood(PlayerItemConsumeEvent $event): void {
        $item = $event->getItem();
        if ($item instanceof CustomFood || $item instanceof CustomPotion) {
            $item->onConsume($event->getPlayer());
        }
    }
}
