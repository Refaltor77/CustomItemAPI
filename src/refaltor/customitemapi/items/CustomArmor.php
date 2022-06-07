<?php

namespace core\items\itemsTemplates;

use pocketmine\block\Block;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class ArmorItem extends Armor
{
    const ARMOR_GROUP = [
        0 => 'itemGroup.name.helmet',
        1 => 'itemGroup.name.chestplate',
        2 => 'itemGroup.name.leggings',
        3 => 'itemGroup.name.boots'
    ];

    const ARMOR_ENCHANT = [
        0 => 'armor_helmet',
        1 => 'armor_torso',
        2 => 'armor_legs',
        3 => 'armor_feet'
    ];

    const ARMOR_WEARABLE = [
        0 => 'slot.armor.head',
        1 => 'slot.armor.chest',
        2 => 'slot.armor.legs',
        3 => 'slot.armor.feet'
    ];
    protected $lore = [];
    private string $texture_path;
    private ?ShapedRecipe $recipe;

    public function __construct(ItemIdentifier $identifier, string $name, ArmorTypeInfo $info, string $texture_path, ?ShapedRecipe $recipe = null, array $lore = [])
    {
        $this->texture_path = $texture_path;
        $this->recipe = $recipe;
        $this->lore = $lore;
        parent::__construct($identifier, $name, $info);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult
    {
        return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
    }

    public function getNbt(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("minecraft:durability", CompoundTag::create()
                    ->setShort("damage_change", 1)
                    ->setInt("max_durability", $this->getMaxDurability())
                )
                ->setTag("minecraft:armor", CompoundTag::create()
                    ->setString("texture_type", 'diamond')
                    ->setInt("protection", $this->getDefensePoints())
                )
                ->setTag("minecraft:wearable", CompoundTag::create()
                    ->setString("slot", self::ARMOR_WEARABLE[$this->getArmorSlot()])
                    ->setByte("dispensable", 1)
                )
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("use_duration", 32)
                    ->setByte('can_destroy_in_creative', 0)
                    ->setInt("use_animation", 0)
                    ->setString("enchantable_slot", "axe")
                    ->setInt("enchantable_value", 10)
                    ->setByte("creative_category", 3)
                    ->setInt("max_stack_size", 1)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", self::ARMOR_GROUP[$this->getArmorSlot()])
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                        ->setString("legacy_id", "goldrush:" . $this->name)
                    )
                )
            )
            ->setShort("minecraft:identifier", $this->getId() + ($this->getId() > 0 ? 5000 : -5000))
            ->setTag("minecraft:display_name", CompoundTag::create()
                ->setString("value", "item." . strtolower($this->name) . "name")
            )
            ->setTag("minecraft:on_use", CompoundTag::create()
                ->setByte("on_use", 1)
            )->setTag("minecraft:on_use_on", CompoundTag::create()
                ->setByte("on_use_on", 1)
            );
    }
}
