<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

class SwordComponents extends Components
{
    public ?int $attackPoints = null;
    public ?int $maxDurability = null;

    public function serializeToNbt(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", $this->max_stack_size)
                    ->setByte("hand_equipped", 1)
                    ->setInt("damage", $this->attackPoints)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", "itemGroup.name.sword")
                    ->setString("enchantable_slot", "sword")
                    ->setInt("enchantable_value", 10)
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                        ->setString("legacy_id", "custom:" . strtolower($this->display_name))
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
