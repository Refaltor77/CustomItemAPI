<?php

namespace customiesdevs\customies\item\types;

use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\RenderOffsetsComponent;
use customiesdevs\customies\item\component\WearableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;

class CustomArmor extends Armor implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier, string $name, ArmorTypeInfo $info, string $texture, int $renderOffset)
    {

        switch ($info->getArmorSlot()) {
            case ArmorInventory::SLOT_HEAD:
                $wearable = WearableComponent::SLOT_ARMOR_HEAD;
                $group = CreativeInventoryInfo::GROUP_HELMET;
                break;
            case ArmorInventory::SLOT_CHEST:
                $wearable = WearableComponent::SLOT_ARMOR_CHEST;
                $group = CreativeInventoryInfo::GROUP_CHESTPLATE;
                break;
            case ArmorInventory::SLOT_LEGS:
                $wearable = WearableComponent::SLOT_ARMOR_LEGS;
                $group = CreativeInventoryInfo::GROUP_LEGGINGS;
                break;
            case ArmorInventory::SLOT_FEET:
                $wearable = WearableComponent::SLOT_ARMOR_FEET;
                $group = CreativeInventoryInfo::GROUP_BOOTS;
                break;
        }

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            $group ?? CreativeInventoryInfo::DEFAULT()
        );

        parent::__construct($identifier, $name, $info);


        $this->initComponent($texture,$inventory);

        $this->addComponent(new ArmorComponent($this->getDefensePoints(), "diamond"));
        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new WearableComponent($wearable ?? WearableComponent::SLOT_ARMOR));
        $this->addComponent(new RenderOffsetsComponent($renderOffset, $renderOffset, false));

    }
}