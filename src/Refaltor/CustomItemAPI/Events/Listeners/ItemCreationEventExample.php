<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\event\Listener;
use pocketmine\Server;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class ItemCreationEventExample implements Listener
{
    public function onCreation(ItemCreationEvents $event): void
    {
        $id = $event->getItemId();
        $name = $event->getItemName();
        Server::getInstance()->getLogger()->info('Item \''.$name.' registered !');
    }
}