<?php

namespace customiesdevs\customies\item\types;

use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\RenderOffsetsComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\component\UseDurationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;

class FoodItem extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    private bool $canAlwaysEat;
    private int $maxStackSize;
    private int $foodRestore;
    private float $saturationRestore;

    public function __construct(ItemIdentifier $identifier, string $name, bool $isPotion, string $texture, int $maxStackSize, bool $canAlwaysEat, int $foodRestore, float $saturationRestore, int $renderOffset)
    {
        parent::__construct($identifier, $name);

        $inventory = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_NATURE, CreativeInventoryInfo::GROUP_MISC_FOOD);
        $this->initComponent($texture, $inventory);
        $this->addComponent(new FoodComponent($canAlwaysEat));
        $this->addComponent(new UseDurationComponent(32));
        $this->addComponent(new UseAnimationComponent($isPotion ? UseAnimationComponent::ANIMATION_DRINK : UseAnimationComponent::ANIMATION_EAT));
        $this->addComponent(new RenderOffsetsComponent($renderOffset, $renderOffset, true));


        $this->canAlwaysEat = $canAlwaysEat;
        $this->maxStackSize = $maxStackSize;
        $this->foodRestore = $foodRestore;
        $this->saturationRestore = $saturationRestore;
    }

    public function getFoodRestore(): int
    {
        return $this->foodRestore;
    }

    public function getSaturationRestore(): float
    {
        return $this->saturationRestore;
    }

    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }
}