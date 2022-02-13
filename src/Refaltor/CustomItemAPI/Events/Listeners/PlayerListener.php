<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\defaults\GamemodeCommand;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Hoe;
use pocketmine\item\ItemFactory;
use pocketmine\player\GameMode;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\BlockPlaceSound;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Items\ArmorItem;
use Refaltor\CustomItemAPI\Items\AxeItem;
use Refaltor\CustomItemAPI\Items\FoodItem;
use Refaltor\CustomItemAPI\Items\HoeItem;
use Refaltor\CustomItemAPI\Items\PickaxeItem;
use Refaltor\CustomItemAPI\Items\ShovelItem;
use Refaltor\CustomItemAPI\Items\StructureItem;
use Refaltor\CustomItemAPI\Items\SwordItem;

class PlayerListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $packet = CustomItemMain::getInstance()->packet;
        if (!is_null($packet)) $player->getNetworkSession()->sendDataPacket(CustomItemMain::getInstance()->packet);
    }

    public function onInteractBlock(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();

        if ($item instanceof Hoe) {
            if ($event->getBlock()->getId() === BlockLegacyIds::DIRT || $event->getBlock()->getId() === BlockLegacyIds::GRASS) {
                $event->getBlock()->getPosition()->getWorld()->addSound($event->getBlock()->getPosition(), new BlockPlaceSound(VanillaBlocks::FARMLAND()));
            }
        }

        if ($item instanceof HoeItem) {
            if ($event->getBlock()->getId() === BlockLegacyIds::DIRT || $event->getBlock()->getId() === BlockLegacyIds::GRASS) {
                $event->getBlock()->getPosition()->getWorld()->setBlock($event->getBlock()->getPosition(), VanillaBlocks::FARMLAND());
                $event->getBlock()->getPosition()->getWorld()->addSound($event->getBlock()->getPosition(), new BlockPlaceSound(VanillaBlocks::FARMLAND()));
            }
        }
    }


    public function onConsume(PlayerItemConsumeEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();

        if ($item instanceof FoodItem) {
            $player->getInventory()->removeItem($item->setCount(1));
            $food = $player->getHungerManager()->getFood();
            $saturation = $player->getHungerManager()->getSaturation();

            if ($food + $item->getFoodRestore() >= 20) {
                $player->getHungerManager()->setFood(20);
            } else $player->getHungerManager()->setFood($food + $item->getFoodRestore());
            if ($saturation + $item->getSaturationRestore() >= 20.00) {
                $player->getHungerManager()->setSaturation(20.00);
            } else $player->getHungerManager()->setFood($saturation + $item->getSaturationRestore());
        }
    }
}