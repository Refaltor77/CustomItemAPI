<?php

namespace Refaltor\CustomItemAPI\Events\Listeners;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\ItemFrame;
use pocketmine\block\Opaque;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Items\AxeItem;
use Refaltor\CustomItemAPI\Items\HoeItem;
use Refaltor\CustomItemAPI\Items\PickaxeItem;
use Refaltor\CustomItemAPI\Items\ShovelItem;
use Refaltor\CustomItemAPI\Items\SwordItem;

class PacketListener implements Listener
{
    private CustomItemMain $plugin;

    public function __construct(CustomItemMain $plugin)
    {
        $this->plugin = $plugin;
    }

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
            var_dump($actions);

            foreach ($actions as $action) {
                if (!$action instanceof PlayerBlockActionWithBlockInfo) return;


                $player = $event->getOrigin()?->getPlayer() ?: throw new AssumptionFailedError("This packet cannot be received from non-logged in player");
                $pos = new Vector3($action->getBlockPosition()->getX(), $action->getBlockPosition()->getY(), $action->getBlockPosition()->getZ());



                if ($action->getActionType() === PlayerAction::START_BREAK) {
                    $item = $player->getInventory()->getItemInHand();

                    if (!$item instanceof PickaxeItem && !$item instanceof AxeItem && !$item instanceof ShovelItem && !$item instanceof SwordItem && !$item instanceof HoeItem) {
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
                    if ($item instanceof PickaxeItem && $target->getBreakInfo()->getToolType() === BlockToolType::PICKAXE
                        || $item instanceof HoeItem && $target->getBreakInfo()->getToolType() === BlockToolType::HOE
                        || $item instanceof AxeItem && $target->getBreakInfo()->getToolType() === BlockToolType::AXE
                        || $item instanceof ShovelItem && $target->getBreakInfo()->getToolType() === BlockToolType::SHOVEL
                        || $item instanceof SwordItem && $target->getBreakInfo()->getToolType() === BlockToolType::SWORD) $pass = true;


                    if ($pass) {
                        if (!$player->isCreative()) {
                            $breakTime = ceil($target->getBreakInfo()->getBreakTime($player->getInventory()->getItemInHand()) * 20);
                            if ($breakTime > 0) {
                                if ($breakTime > 10) {
                                    $breakTime -= 10;
                                }
                                if ($target instanceof Opaque || $target->getId() === 4) {
                                    $breakTime -= 3;
                                }
                                if ($breakTime <= 0) $breakTime = 1;
                                $item->onDestroyBlock($target);
                                $this->scheduleTask(Position::fromObject($pos, $player->getWorld()), $player->getInventory()->getItemInHand(), $player, $breakTime);
                                $player->getWorld()->broadcastPacketToViewers($pos, LevelEventPacket::create(LevelEvent::BLOCK_START_BREAK, (int)(65535 / $breakTime), $pos->asVector3()));
                            }
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



    public function onPlayerQuit(PlayerQuitEvent $event) : void{
        $player = $event->getPlayer();
        if(!isset($this->handlers[$player->getName()])){
            return;
        }
        foreach($this->handlers[$player->getName()] as $blockHash => $handler){
            $handler->cancel();
        }
        unset($this->handlers[$player->getName()]);
    }

    private function scheduleTask(Position $pos, Item $item, Player $player, float $breakTime) : void{
        $handler = $this->getPlugin()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($pos, $item, $player) : void{
            $pos->getWorld()->useBreakOn($pos, $item, $player);
            if ($item->getDamage() + 1 >= $item->getMaxDurability()) {
                $player->getInventory()->setItemInHand(ItemFactory::air());
            } else {
                $item->setDamage($item->getDamage() + 1);
                $player->getInventory()->setItemInHand($item);
            }
            $item->applyDamage(1);
            unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
        }), (int) floor($breakTime));
        if(!isset($this->handlers[$player->getName()])){
            $this->handlers[$player->getName()] = [];
        }
        $this->handlers[$player->getName()][$this->blockHash($pos)] = $handler;
    }

    private function stopTask(Player $player, Position $pos) : void{
        if(!isset($this->handlers[$player->getName()][$this->blockHash($pos)])){
            return;
        }
        $handler = $this->handlers[$player->getName()][$this->blockHash($pos)];
        $handler->cancel();
        unset($this->handlers[$player->getName()][$this->blockHash($pos)]);
    }

    private function blockHash(Position $pos) : string{
        return implode(":", [$pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $pos->getWorld()->getFolderName()]);
    }


    public function getPlugin(): CustomItemMain
    {
        return $this->plugin;
    }
}