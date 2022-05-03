<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

abstract class StructureItem extends Item implements CustomItem
{
    private string $texture_path;
    private int $maxStackSize;
    protected $lore;

    public function __construct(ItemIdentifier $identifier, string $name, string $texture_path = 'Unknown', int $maxStackSize = 64, array $lore = [])
    {
        $this->texture_path = $texture_path;
        $this->maxStackSize = $maxStackSize;
        $this->lore = $lore;
        parent::__construct($identifier, $name);
    }

    public function addToServer(bool $eventCall = true): void
    {
        if ($eventCall) {
            (new ItemCreationEvents($this))->call();
        } else CustomItemMain::getInstance()->items[] = $this;
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