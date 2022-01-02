<?php

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\tag\CompoundTag;
use Refaltor\CustomItemAPI\Dependency\BasicComponents;

class BasicItem extends StructureItem
{
    private string $texture_path;
    private int $maxStackSize;

    public function __construct(ItemIdentifier $identifier, string $name, string $texture_path = 'Unknown', int $maxStackSize = 64)
    {
        $this->texture_path = $texture_path;
        $this->maxStackSize = $maxStackSize;
        parent::__construct($identifier, $name);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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
        $components = new BasicComponents();
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