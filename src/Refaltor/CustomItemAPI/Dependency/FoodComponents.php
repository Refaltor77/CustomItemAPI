<?php

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

class FoodComponents extends Components
{
    public ?int $foodRestore = null;

    public function serializeToNbt(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", $this->max_stack_size)
                    ->setInt("use_duration", 32)
                    ->setInt("use_animation", 1)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", "itemGroup.name.miscFood")
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                        ->setString("legacy_id", "custom:" . strtolower($this->display_name))
                    )
                )
                ->setTag('minecraft:food', CompoundTag::create()
                    ->setByte('can_always_eat', 0)
                    ->setFloat('nutrition', $this->foodRestore)
                    ->setString('saturation_modifier', 'higth')
                )
                ->setShort("minecraft:identifier", $this->id + ($this->id > 0 ? 5000 : -5000))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", 'item.' . str_replace(' ', '_', strtolower($this->display_name)) . '.name')
                )
            );
    }
}