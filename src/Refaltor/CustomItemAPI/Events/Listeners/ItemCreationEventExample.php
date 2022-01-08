<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\event\Listener;
use pocketmine\Server;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class ItemCreationEventExample implements Listener
{
    private CustomItemMain $plugin;

    public function __construct(CustomItemMain $customItemMain)
    {
        $this->plugin = $customItemMain;
    }

    public function onCreation(ItemCreationEvents $event): void
    {
        $id = $event->getItemId();
        $name = $event->getItemName();

        $file = fopen($this->plugin->getDataFolder() . 'debugs/debugs.txt', 'a+');
        $log = "register item [$name] with id [$id]";
        fwrite($file, $log);
        fclose($file);
    }
}