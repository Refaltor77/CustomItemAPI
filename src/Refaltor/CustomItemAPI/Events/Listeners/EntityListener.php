<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use Refaltor\CustomItemAPI\Items\SwordItem;

class EntityListener implements Listener
{
    public function onDamage(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();
        $event->setAttackCooldown(7.5);
        if ($damager instanceof Player) {
            $itemInHand = $damager->getInventory()->getItemInHand();
            if ($itemInHand instanceof SwordItem) {
                $attackCooldown = $itemInHand->getAttackCooldown();
                $knockBack = $itemInHand->getKnockback();
                if (!is_null($attackCooldown)) $event->setAttackCooldown($attackCooldown);
                if (!is_null($knockBack)) $event->setKnockBack($knockBack);
            }
        }
    }
}