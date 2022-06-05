<?php

/*
 *    _______           _______ _________ _______  _______ __________________ _______  _______  _______  _______ _________
 *   (  ____ \|\     /|(  ____ \\__   __/(  ___  )(       )\__   __/\__   __/(  ____ \(       )(  ___  )(  ____ )\__   __/
 *   | (    \/| )   ( || (    \/   ) (   | (   ) || () () |   ) (      ) (   | (    \/| () () || (   ) || (    )|   ) (
 *   | |      | |   | || (_____    | |   | |   | || || || |   | |      | |   | (__    | || || || (___) || (____)|   | |
 *   | |      | |   | |(_____  )   | |   | |   | || |(_)| |   | |      | |   |  __)   | |(_)| ||  ___  ||  _____)   | |
 *   | |      | |   | |      ) |   | |   | |   | || |   | |   | |      | |   | (      | |   | || (   ) || (         | |
 *   | (____/\| (___) |/\____) |   | |   | (___) || )   ( |___) (___   | |   | (____/\| )   ( || )   ( || )      ___) (___
 *   (_______/(_______)\_______)   )_(   (_______)|/     \|\_______/   )_(   (_______/|/     \||/     \||/       \_______/
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   By: refaltor
 *   Discord: Refaltor#6969
 */

declare(strict_types=1);

namespace refaltor\customitemapi\items;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\Shovel;
use pocketmine\item\ToolTier;
use pocketmine\nbt\tag\CompoundTag;
use refaltor\customitemapi\traits\UtilsTrait;

class CustomShovel extends Shovel
{

    use UtilsTrait;

    private string $textureName;
    private float $miningSpeed;
    private int $durability;
    private int $attackPoints;


    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        ToolTier $tier,
        string $textureName,
        float $miningSpeed,
        int $durability,
        int $attackPoints
    )
    {
        $this->textureName = $textureName;
        $this->miningSpeed = $miningSpeed;
        $this->durability = $durability;
        $this->attackPoints = $attackPoints;
        parent::__construct($identifier, $name, $tier);
    }

    public function getComponents(): CompoundTag
    {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("item_properties", CompoundTag::create()
                    ->setInt("max_stack_size", 1)
                    ->setByte("hand_equipped", 1)
                    ->setInt("damage", $this->attackPoints)
                    ->setInt("creative_category", 3)
                    ->setString("creative_group", "itemGroup.name.shovel")
                    ->setString("enchantable_slot", "shovel")
                    ->setInt("enchantable_value", 10)
                    ->setByte('can_destroy_in_creative', 1)
                    ->setTag("minecraft:icon", CompoundTag::create()
                        ->setString("texture", $this->getTextureName())
                    )
                )
                ->setTag("minecraft:weapon", CompoundTag::create()
                    ->setTag("on_hurt_entity", CompoundTag::create()
                        ->setString("event", "event")
                    )
                )
                ->setTag("minecraft:durability", CompoundTag::create()
                    ->setInt("max_durability", $this->getMaxDurability())
                )
                ->setShort("minecraft:identifier", $this->getRuntimeId($this->getId()))
                ->setTag("minecraft:display_name", CompoundTag::create()
                    ->setString("value", 'item.' . str_replace(' ', '_', strtolower($this->getName())) . '.name')
                )
            );
    }


    public function getTextureName(): string {
        return $this->textureName;
    }

    public function getMaxDurability(): int{
        return $this->durability;
    }

    public function getAttackPoints(): int{
        return $this->attackPoints;
    }

    public function getBaseMiningEfficiency(): float{
        return $this->miningSpeed;
    }
}