<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use Refaltor\CustomItemAPI\Items\ArmorItem;
use Refaltor\CustomItemAPI\Items\AxeItem;
use Refaltor\CustomItemAPI\Items\FoodItem;
use Refaltor\CustomItemAPI\Items\HoeItem;
use Refaltor\CustomItemAPI\Items\PickaxeItem;
use Refaltor\CustomItemAPI\Items\ShovelItem;
use Refaltor\CustomItemAPI\Items\StructureItem;
use Refaltor\CustomItemAPI\Items\SwordItem;

class ItemCreationEvents extends Event implements Cancellable
{
    use CancellableTrait;

    private StructureItem|ArmorItem|SwordItem|FoodItem|PickaxeItem|AxeItem|ShovelItem|HoeItem $item;
    private int $itemRuntimeId;
    private int $itemId;
    private string $itemName;
    private string $texturePath;
    private int $maxStackSize;

    public function __construct(StructureItem|ArmorItem|SwordItem|FoodItem|PickaxeItem|AxeItem|ShovelItem|HoeItem $item)
    {
        $this->item = $item;
        $this->itemRuntimeId = $item->getId() + ($item->getId() > 0 ? 5000 : -5000);
        $this->itemId = $item->getId();
        $this->itemName = $item->getName();
        $this->maxStackSize = $item->getMaxStackSize();
        $this->texturePath = $item->getTexturePath();
    }

    public function call(): void
    {
        parent::call();
        if (!$this->isCancelled()) $this->item->addToServer(false);
    }

    /**
     * @return int
     */
    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }

    /**
     * @return int|string
     */
    public function getTexturePath()
    {
        return $this->texturePath;
    }

    /**
     * @return string
     */
    public function getItemName(): string
    {
        return $this->itemName;
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->itemId;
    }

    /**
     * @return int|string
     */
    public function getItemRuntimeId()
    {
        return $this->itemRuntimeId;
    }

    /**
     * @return StructureItem
     */
    public function getItem(): StructureItem
    {
        return $this->item;
    }
}