<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

class ArmorComponents extends Components
{
    public ?string $armorGroup = null;
    public ?int $armorSlot = null;
    public ?int $max_durability = null;
    public ?int $defensePoints = null;

    const ARMOR_ENCHANT = [
        0 => 'armor_helmet',
        1 => 'armor_torso',
        2 => 'armor_legs',
        3 => 'armor_feet'
    ];

    public function serializeToNbt(): CompoundTag
    {
        return  CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", 1)
                    ->setInt("use_duration", 32)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", $this->armorGroup)
                    ->setString("enchantable_slot", self::ARMOR_ENCHANT[$this->armorSlot])
                    ->setInt("enchantable_value", 10)
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                        ->setString("legacy_id", "custom:" . strtolower($this->display_name))
                    )
                )
                ->setTag("minecraft:durability", CompoundTag::create()
                    ->setInt("max_durability", $this->max_durability)
                )
                ->setTag("minecraft:armor", CompoundTag::create()
                    ->setInt("protection", $this->defensePoints)
                )
                ->setTag("minecraft:wearable", CompoundTag::create()
                    ->setInt("slot", intval($this->armorSlot + 2))
                )
                ->setShort("minecraft:identifier", $this->id + ($this->id > 0 ? 5000 : -5000))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", 'item.' . str_replace(' ', '_', strtolower($this->display_name)) . '.name')
                )
            );
    }
}
