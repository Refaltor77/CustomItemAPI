<?php

namespace customiesdevs\customies\player;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Durable;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\ItemBreakSound;

class CustomPlayer extends Player {
    protected ?SurvivalBlockHandler $blockBreakHandlerCustom = null;

    public function onUpdate(int $currentTick): bool
    {
        $this->blockBreakHandlerCustom?->update() ?: $this->blockBreakHandlerCustom = null;
        return parent::onUpdate($currentTick);
    }

    public function attackBlock(Vector3 $pos, int $face): bool
    {
        if ($pos->distanceSquared($this->location) > 10000) {
            return false;
        }
        $target = $this->getWorld()->getBlock($pos);

        $ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);

        if ($this->isSpectator()) {
            $ev->cancel();
        }

        $ev->call();
        if ($ev->isCancelled()) {
            return false;
        }
        $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
        if ($target->onAttack($this->inventory->getItemInHand(), $face, $this)) {
            return true;
        }
        $block = $target->getSide($face);
        if ($block->getTypeId() === VanillaBlocks::FIRE()->getTypeId()) {
            $this->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
            $this->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
            return true;
        }


        if (!$this->isCreative() && !$block->getBreakInfo()->breaksInstantly()) {
            $this->blockBreakHandlerCustom = new SurvivalBlockHandler($this, $pos, $target, $face, 16);
        }

        return true;
    }

    public function continueBreakBlock(Vector3 $pos, int $face): void
    {
        if ($this->blockBreakHandlerCustom !== null && $this->blockBreakHandlerCustom->getBlockPos()->distanceSquared($pos) < 0.0001) {
            $this->blockBreakHandlerCustom->setTargetedFace($face);
            $this->blockBreakHandlerCustom->setTargetedFace($face);
            if (($this->blockBreakHandlerCustom->getBreakProgress() + $this->blockBreakHandlerCustom->getBreakSpeed()) >= 0.80) {
                $pos = $this->blockBreakHandlerCustom->getBlockPos();
                $this->breakBlock($pos);
            }
        }
    }

    public function breakBlock(Vector3 $pos): bool
    {
        $this->removeCurrentWindow();
        if ($this->canInteract($pos->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 7)) {
            $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
            $this->stopBreakBlock($pos);
            $item = $this->inventory->getItemInHand();
            $oldItem = clone $item;
            if ($this->getWorld()->useBreakOn($pos, $item, $this, true)) {
                if ($this->hasFiniteResources() && !$item->equalsExact($oldItem) && $oldItem->equalsExact($this->inventory->getItemInHand())) {
                    if ($item instanceof Durable && $item->isBroken()) {
                        $this->broadcastSound(new ItemBreakSound());
                    }
                    $this->inventory->setItemInHand($item);
                }
                $this->hungerManager->exhaust(0.005, PlayerExhaustEvent::CAUSE_MINING);
                return true;
            }
        } else {
            $this->logger->debug("Cancelled block break at $pos due to not currently being interactable");
        }

        return false;
    }


    # BLOCK HANDLER

    public function stopBreakBlock(Vector3 $pos): void
    {
        if ($this->blockBreakHandlerCustom !== null && $this->blockBreakHandlerCustom->getBlockPos()->distanceSquared($pos) < 0.0001) {
            $this->blockBreakHandlerCustom = null;
        }
    }

    protected function destroyCycles(): void
    {
        parent::destroyCycles();
        $this->blockBreakHandlerCustom = null;
    }
}