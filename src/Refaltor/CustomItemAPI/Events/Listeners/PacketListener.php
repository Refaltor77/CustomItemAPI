<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;

class PacketListener implements Listener
{
    public function onDataPacketSend(DataPacketSendEvent $event) : void{
        $packets = $event->getPackets();
        foreach($packets as $packet){
            if($packet instanceof StartGamePacket){
                $packet->levelSettings->experiments = new Experiments([
                    "data_driven_items" => true
                ], true);
            }elseif($packet instanceof ResourcePackStackPacket){
                $packet->experiments = new Experiments([
                    "data_driven_items" => true
                ], true);
            }
        }
    }
}