<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Dependency\ArmorComponents;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class ArmorItem extends Armor implements CustomItem
{
    private string $texture_path;
    private int $max_durability;

    private $listenerInteract;
    private $listenerDestroyBlock;
    private $listenerClickAir;
    private $listenerAttackEntity;
    private $listenerOnBroken;
    protected $lore;

    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        ArmorTypeInfo $info,
        string $texturePath,
        ?callable $listenerInteract = null,
        ?callable $listenerDestroyBlock = null,
        ?callable $listenerClickAir = null,
        ?callable $listenerAttackEntity = null,
        ?callable $listenerOnBroken = null,
        array $lore = []
    )
    {
        $this->texture_path = $texturePath;
        $this->max_durability = $info->getMaxDurability();
        $this->listenerAttackEntity = $listenerAttackEntity;
        $this->listenerClickAir = $listenerClickAir;
        $this->listenerInteract = $listenerInteract;
        $this->listenerDestroyBlock = $listenerDestroyBlock;
        $this->listenerOnBroken = $listenerOnBroken;
        $this->lore = $lore;
        parent::__construct($identifier, $name, $info);
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

    public function onBroken(): void
    {
        if (is_callable($this->listenerOnBroken)) call_user_func($this->listenerOnBroken, $this);
        parent::onBroken();
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
        if (is_callable($this->listenerDestroyBlock)) call_user_func($this->listenerDestroyBlock, $block, $this);
        return parent::onDestroyBlock($block);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult
    {
        if (is_callable($this->listenerInteract)) call_user_func($this->listenerInteract, $player, $blockReplace, $blockClicked, $face, $clickVector, $this);
        return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function setTexturePath(string $texture_path): void
    {
        $this->texture_path = $texture_path;
    }

    public function getTexturePath(): string
    {
        return $this->texture_path;
    }

    public function addToServer(bool $eventCall = true): void
    {
        if ($eventCall) {
            (new ItemCreationEvents($this))->call();
        } else CustomItemMain::getInstance()->items[] = $this;
    }

    public function getComponents(): CompoundTag
    {
        $components = new ArmorComponents();
        $components->texture_path = $this->getTexturePath();
        $components->display_name = $this->getName();
        $components->max_stack_size = $this->getMaxStackSize();
        $components->max_durability = $this->getMaxDurability();
        $components->armorSlot = $this->getArmorSlot();
        $components->id = $this->getId();
        $slot = match ($this->getArmorSlot()) {
            ArmorInventory::SLOT_HEAD => "itemGroup.name.helmet" ,
            ArmorInventory::SLOT_CHEST => "itemGroup.name.chestplate" ,
            ArmorInventory::SLOT_FEET => "itemGroup.name.boots" ,
            ArmorInventory::SLOT_LEGS => "itemGroup.name.leggings" ,
        };
        $components->armorGroup = $slot;
        $components->defensePoints = $this->getDefensePoints();
        return $components->serializeToNbt();
    }
}