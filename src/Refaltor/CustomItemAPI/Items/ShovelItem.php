<?php

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\BlockBreakSound;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Dependency\ShovelComponents;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class ShovelItem extends Shovel
{
    private string $texture_path;
    private int $maxStackSize = 1;
    private float $mining_efficiency;
    private int $maxDurability;

    private $listenerInteract;
    private $listenerDestroyBlock;
    private $listenerClickAir;
    private $listenerAttackEntity;
    private $listenerOnBroken;
    protected $lore;

    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        ToolTier $tier,
        int $maxDurability,
        float $mining_efficiency,
        string $texture_path = 'Unknown',
        ?callable $listenerInteract = null,
        ?callable $listenerDestroyBlock = null,
        ?callable $listenerClickAir = null,
        ?callable $listenerAttackEntity = null,
        ?callable $listenerOnBroken = null,
        array $lore = []
    )
    {
        $this->maxDurability = $maxDurability;
        $this->texture_path = $texture_path;
        $this->mining_efficiency = $mining_efficiency;
        $this->listenerAttackEntity = $listenerAttackEntity;
        $this->listenerClickAir = $listenerClickAir;
        $this->listenerInteract = $listenerInteract;
        $this->listenerDestroyBlock = $listenerDestroyBlock;
        $this->listenerOnBroken = $listenerOnBroken;
        $this->lore = $lore;
        parent::__construct($identifier, $name, $tier);
    }

    public function setInteractListener(callable $callable): void
    {
        $this->listenerInteract = $callable;
    }

    public function setDestroyBlockListener(callable $callable): void
    {
        $this->listenerDestroyBlock = $callable;
    }

    public function setClickAirListener(callable $callable): void
    {
        $this->listenerClickAir = $callable;
    }

    public function setAttackEntityListener(callable $callable): void
    {
        $this->listenerAttackEntity = $callable;
    }

    public function setBrokenListener(callable $callable): void
    {
        $this->listenerOnBroken = $callable;
    }

    public function getMaxDurability(): int
    {
        return $this->maxDurability;
    }

    public function getBaseMiningEfficiency(): float
    {
        return $this->mining_efficiency;
    }

    public function onAttackEntity(Entity $victim): bool
    {
        if (is_callable($this->listenerAttackEntity)) call_user_func($this->listenerAttackEntity, $victim, $this);
        return parent::onAttackEntity($victim);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        if (is_callable($this->listenerClickAir)) call_user_func($this->listenerClickAir, $player, $directionVector, $this);
        return parent::onClickAir($player, $directionVector);
    }

    public function onDestroyBlock(Block $block): bool
    {
        $block->getPosition()->getWorld()->addSound($block->getPosition(), new BlockBreakSound($block));
        $block->getPosition()->getWorld()->addParticle($block->getPosition(), new BlockBreakParticle($block));
        if (is_callable($this->listenerDestroyBlock)) call_user_func($this->listenerDestroyBlock, $block, $this);
        return parent::onDestroyBlock($block);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult
    {
        if (is_callable($this->listenerInteract)) call_user_func($this->listenerInteract, $player, $blockReplace, $blockClicked, $face, $clickVector, $this);
        return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
    }

    public function onBroken(): void
    {
        if (is_callable($this->listenerOnBroken)) call_user_func($this->listenerOnBroken, $this);
        parent::onBroken();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addToServer(bool $eventCall = true): void
    {
        if ($eventCall) {
            (new ItemCreationEvents($this))->call();
        } else CustomItemMain::getInstance()->items[] = $this;
    }

    public function setMaxStackSize(int $maxStackSize): void
    {
        $this->maxStackSize = $maxStackSize;
    }

    public function setTexturePath(string $texture_path): void
    {
        $this->texture_path = $texture_path;
    }

    public function getComponents(): CompoundTag
    {
        $components = new ShovelComponents();
        $components->maxDurability = $this->getMaxDurability();
        $components->attackPoints = $this->getAttackPoints();
        $components->display_name = $this->getName();
        $components->id = $this->getId();
        $components->max_stack_size = $this->getMaxStackSize();
        $components->texture_path = $this->getTexturePath();
        return $components->serializeToNbt();
    }

    public function getTexturePath(): string
    {
        return $this->texture_path;
    }

    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }
}