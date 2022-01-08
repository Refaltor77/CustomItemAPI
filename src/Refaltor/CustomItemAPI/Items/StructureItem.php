<?php

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

abstract class StructureItem extends Item
{
    private string $texture_path;
    private int $maxStackSize;

    public function __construct(ItemIdentifier $identifier, string $name, string $texture_path = 'Unknown', int $maxStackSize = 64)
    {
        $this->texture_path = $texture_path;
        $this->maxStackSize = $maxStackSize;
        parent::__construct($identifier, $name);
    }

    public function addToServer(bool $eventCall = true): void
    {
        if ($eventCall) {
            (new ItemCreationEvents($this))->call();
        } else CustomItemMain::getInstance()->items[] = $this;
    }

    abstract public function getComponents(): CompoundTag;

    public function getTexturePath(): string
    {
        return $this->texture_path;
    }

    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }
}