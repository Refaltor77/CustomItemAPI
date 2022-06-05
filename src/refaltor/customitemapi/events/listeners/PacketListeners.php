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

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\ItemFrame;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\ItemBreakSound;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\interfaces\ExperimentsString;
use refaltor\customitemapi\items\CustomAxe;
use refaltor\customitemapi\items\CustomHoe;
use refaltor\customitemapi\items\CustomPickaxe;
use refaltor\customitemapi\items\CustomShovel;
use refaltor\customitemapi\items\CustomSword;
use refaltor\customitemapi\traits\OwnedTrait;
use refaltor\customitemapi\traits\UtilsTrait;

class PacketListeners implements Listener
{
    use OwnedTrait;
    use UtilsTrait;

    public function __construct(CustomItemAPI $plugin)
    {
        $this->setPlugin($plugin);
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        $packets = $event->getPackets();

        $experimentsOverridden = [
            ExperimentsString::DATA_DRIVEN_ITEMS => true,
            ExperimentsString::EXPERIMENTAL_MOLANG_FEATURES => true,
            ExperimentsString::GAMETEST => true,
            ExperimentsString::SCRIPTING => true,
            ExperimentsString::UPCOMING_CREATOR_FEATURES => true
        ];

        foreach ($packets as $packet) {
            if ($packet instanceof StartGamePacket) {


                $packet->levelSettings->experiments = new Experiments([
                    "data_driven_items" => true
                ], true);

                $experiments = $packet->levelSettings->experiments;
                /**
                 * @noinspection PhpExpressionResultUnusedInspection
                 * HACK : Modifying properties using public constructors
                 */
                $experiments->__construct(
                    array_merge($experiments->getExperiments(), $experimentsOverridden),
                    $experiments->hasPreviouslyUsedExperiments()
                );
            } elseif ($packet instanceof ResourcePackStackPacket) {

                $packet->experiments = new Experiments([
                    "data_driven_items" => true
                ], true);

                $experiments = $packet->experiments;
                /**
                 * @noinspection PhpExpressionResultUnusedInspection
                 * HACK : Modifying properties using public constructors
                 */
                $experiments->__construct(
                    array_merge($experiments->getExperiments(), $experimentsOverridden),
                    $experiments->hasPreviouslyUsedExperiments()
                );
            }
        }
    }

    private const OVERRIDDEN_EXPERIMENTS = [
        "scripting" => true, // Additional Modding Capabilities
        "upcoming_creator_features" => true, // Upcoming Creator Features
        "gametest" => true, // Enable GameTest Framework
        "data_driven_items" => true, // Holiday Creator Features
        "experimental_molang_features" => true, // Experimental Molang Features
    ];
    protected array $handlers = [];


    /**
     * @param DataPacketReceiveEvent $event
     *
     * @priority HIGHEST
     */

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();


        if (!$packet instanceof PlayerAuthInputPacket) {
            return;
        }
        try {

            $actions = $packet->getBlockActions();
            if (is_null($actions)) return;

            foreach ($actions as $action) {
                if (!$action instanceof PlayerBlockActionWithBlockInfo) return;


                $player = $event->getOrigin()?->getPlayer() ?: throw new AssumptionFailedError("This packet cannot be received from non-logged in player");
                $pos = new Vector3($action->getBlockPosition()->getX(), $action->getBlockPosition()->getY(), $action->getBlockPosition()->getZ());


                if ($action->getActionType() === PlayerAction::START_BREAK) {
                    $item = $player->getInventory()->getItemInHand();

                    if (!in_array($item::class , [
                        CustomPickaxe::class,
                        CustomAxe::class,
                        CustomShovel::class,
                        CustomSword::class,
                        CustomHoe::class
                    ])) {
                        return;
                    }


                    if ($pos->distanceSquared($player->getPosition()) > 10000) {
                        return;
                    }


                    $target = $player->getWorld()->getBlock($pos);

                    $ev = new PlayerInteractEvent($player, $player->getInventory()->getItemInHand(), $target, null, $action->getFace(), PlayerInteractEvent::LEFT_CLICK_BLOCK);
                    if ($player->isSpectator()) {
                        $ev->cancel();
                    }

                    $ev->call();
                    if ($ev->isCancelled()) {
                        $event->getOrigin()->getInvManager()?->syncSlot($player->getInventory(), $player->getInventory()->getHeldItemIndex());
                        return;
                    }

                    $frameBlock = $player->getWorld()->getBlock($pos);
                    if ($frameBlock instanceof ItemFrame && $frameBlock->getFramedItem() !== null) {
                        if (lcg_value() <= $frameBlock->getItemDropChance()) {
                            $player->getWorld()->dropItem($frameBlock->getPosition(), $frameBlock->getFramedItem());
                        }
                        $frameBlock->setFramedItem(null);
                        $frameBlock->setItemRotation(0);
                        $player->getWorld()->setBlock($pos, $frameBlock);
                        return;
                    }
                    $block = $target->getSide($action->getFace());
                    if ($block->getId() === BlockLegacyIds::FIRE) {
                        $player->getWorld()->setBlock($block->getPosition(), BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
                        return;
                    }

                    $pass = false;
                    if (
                        $item instanceof CustomPickaxe && $target->getBreakInfo()->getToolType() === BlockToolType::PICKAXE ||
                        $item instanceof CustomAxe && $target->getBreakInfo()->getToolType() === BlockToolType::AXE ||
                        $item instanceof CustomShovel && $target->getBreakInfo()->getToolType() === BlockToolType::SHOVEL ||
                        $item instanceof CustomSword ||
                        $item instanceof CustomHoe && $target->getBreakInfo()->getToolType() === BlockToolType::HOE
                    ) $pass = true;


                    if ($pass) {
                        if (!$player->isCreative()) {
                            $breakTime = ceil($target->getBreakInfo()->getBreakTime($player->getInventory()->getItemInHand()) * 20);
                            $this->scheduleTask(Position::fromObject($pos, $player->getWorld()), $player->getInventory()->getItemInHand(), $player, $breakTime, $player->getInventory()->getHeldItemIndex());
                            $player->getWorld()->broadcastPacketToViewers($pos, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::BREAK_BLOCK, $pos, false));
                        }
                    }
                } elseif ($action->getActionType() === PlayerAction::ABORT_BREAK) {
                    $player->getWorld()->broadcastPacketToViewers($pos, LevelEventPacket::create(LevelEvent::BLOCK_STOP_BREAK, 0, $pos->asVector3()));
                    $this->stopTask($player, Position::fromObject($pos, $player->getWorld()));
                }
            }

        } finally {

        }
    }


    private function scheduleTask(Position $pos, Item $item, Player $player, float $breakTime, int $slot): void
    {

        $handler = $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($pos, $item, $player, $breakTime, $slot): void {
            $pos->getWorld()->useBreakOn($pos, $item, $player);
            if ($item->getDamage() + 1 >= $item->getMaxDurability()) {
                $player->getInventory()->setItem($slot, ItemFactory::air());
                $player->getWorld()->addSound($player->getEyePos(), new ItemBreakSound());
            } else {
                $item->setDamage($item->getDamage() + 1);
                $player->getInventory()->setItem($slot, $item);
            }
            $player->getWorld()->broadcastPacketToViewers($pos, LevelEventPacket::create(LevelEvent::BLOCK_START_BREAK, (int)(65535 / $breakTime), $pos->asVector3()));
            $item->applyDamage(1);
            unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
        }), (int)floor($breakTime));
        if (!isset($this->handlers[$player->getName()])) {
            $this->handlers[$player->getName()] = [];
        }
        $this->handlers[$player->getName()][$this->blockHash($pos)] = $handler;
    }

    private function blockHash(Position $pos): string
    {
        return implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()]);
    }

    private function stopTask(Player $player, Position $pos): void
    {
        if (!isset($this->handlers[$player->getName()][$this->blockHash($pos)])) {
            return;
        }
        $handler = $this->handlers[$player->getName()][$this->blockHash($pos)];
        $handler->cancel();
        $player->getWorld()->broadcastPacketToViewers($pos, LevelEventPacket::create(LevelEvent::BLOCK_STOP_BREAK, 1, $pos->asVector3()));
        unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if (!isset($this->handlers[$player->getName()])) {
            return;
        }
        foreach ($this->handlers[$player->getName()] as $blockHash => $handler) {
            $handler->cancel();
        }
        unset($this->handlers[$player->getName()]);
    }


    public function onBreakBlock(BlockBreakEvent $event): void
    {
        $item = $event->getItem();
        if (
            $item instanceof CustomPickaxe ||
            $item instanceof CustomAxe ||
            $item instanceof CustomShovel ||
            $item instanceof CustomSword ||
            $item instanceof CustomHoe
        ) {
            $event->getBlock()->getPosition()->getWorld()->addSound($event->getBlock()->getPosition(), new BlockBreakSound($event->getBlock()));
            $event->getBlock()->getPosition()->getWorld()->addParticle($event->getBlock()->getPosition(), new BlockBreakParticle($event->getBlock()));
        }
    }
}