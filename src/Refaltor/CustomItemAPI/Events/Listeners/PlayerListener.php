<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use Refaltor\CustomItemAPI\CustomItemMain;

class PlayerListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $player->getNetworkSession()->sendDataPacket(CustomItemMain::getInstance()->packet);
    }
}