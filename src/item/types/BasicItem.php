<?php

namespace customiesdevs\customies\item\types;

use customiesdevs\customies\item\component\AllowOffHandComponent;
use customiesdevs\customies\item\component\RenderOffsetsComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

class BasicItem extends Item implements ItemComponents {
    use ItemComponentsTrait;

    private int $maxStackSize = 64;

    public function __construct(ItemIdentifier $identifier, string $name, string $texture, bool $allowOffHand, int $maxStackSize, int $renderOffset)
    {
        $this->maxStackSize = $maxStackSize;
        parent::__construct($identifier, $name);

        $inventory = CreativeInventoryInfo::DEFAULT();
        $this->initComponent($texture, $inventory);
        $this->addComponent(new AllowOffHandComponent($allowOffHand));
        $this->addComponent(new RenderOffsetsComponent($renderOffset, $renderOffset, true));

    }

    public function getMaxStackSize(): int{
        return $this->maxStackSize;
    }
}