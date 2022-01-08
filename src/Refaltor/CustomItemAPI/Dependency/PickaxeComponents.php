<?php

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

class PickaxeComponents extends Components
{
    public ?int $attackPoints = null;
    public ?int $maxDurability = null;

    public function serializeToNbt(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", 1)
                    ->setByte("hand_equipped", true)
                    ->setInt("damage", $this->attackPoints)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", "itemGroup.name.pickaxe")
                    ->setString("enchantable_slot", "pickaxe")
                    ->setInt("enchantable_value", 10)
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                    )
                )
                ->setTag("minecraft:weapon", CompoundTag::create()
                    ->setTag("on_hurt_entity", CompoundTag::create()
                        ->setString("event", "event")
                    )
                )
                ->setTag("minecraft:durability", CompoundTag::create()
                    ->setInt("max_durability", $this->maxDurability)
                )
                ->setShort("minecraft:identifier", $this->id + ($this->id > 0 ? 5000 : -5000))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", 'item.' . str_replace(' ', '_', strtolower($this->display_name)) . '.name')
                )
            );
    }
}