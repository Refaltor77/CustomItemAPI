<?php

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Dependency\ArmorComponents;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class ArmorItem extends Armor
{
    private string $texture_path;
    private int $max_durability;

    public function __construct(ItemIdentifier $identifier, string $name, ArmorTypeInfo $info, string $texturePath = 'Unknown')
    {
        $this->texture_path = $texturePath;
        $this->max_durability = $info->getMaxDurability();
        parent::__construct($identifier, $name, $info);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }


    public function setTexturePath(string $texture_path): void
    {
        $this->texture_path = $texture_path;
    }

    public function getMaxDurability(): int
    {
        return $this->max_durability;
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
        $components->id = $this->getId();
        $components->armorGroup = "itemGroup.name.helmet";
        $components->defensePoints = $this->getDefensePoints();
        return $components->serializeToNbt();
    }
}