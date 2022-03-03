<?php

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

class BasicComponents extends Components
{
    public function serializeToNbt(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", $this->max_stack_size)
                    ->setInt("creative_category", 3)
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->texture_path)
                    )
                )
                ->setShort("minecraft:identifier", $this->id + ($this->id > 0 ? 5000 : -5000))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", 'item.' . str_replace(' ', '_', strtolower($this->display_name)) . '.name')
                )
            );
    }
}