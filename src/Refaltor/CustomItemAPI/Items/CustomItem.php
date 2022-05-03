<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\nbt\tag\CompoundTag;

interface CustomItem{

    public function addToServer(bool $eventCall): void;

    public function getComponents(): CompoundTag;

}
