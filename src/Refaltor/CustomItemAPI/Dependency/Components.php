<?php

namespace Refaltor\CustomItemAPI\Dependency;

use pocketmine\nbt\tag\CompoundTag;

abstract class Components
{
    public ?int $id = null;
    public ?string $display_name = null;
    public ?int $max_stack_size = null;
    public ?string $texture_path = null;

    abstract public function serializeToNbt(): CompoundTag;
}