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

use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use refaltor\customitemapi\traits\UtilsTrait;

class CustomArmor extends Armor
{

    use UtilsTrait;

    protected $lore  = [''];

    const ARMOR_CLASS = [
        "gold",
        "none",
        "leather",
        "chain",
        "iron",
        "diamond",
        "elytra",
        "turtle",
        "netherite"
    ];

    const ARMOR_GROUP = [
        0 => 'itemGroup.name.helmet',
        1 => 'itemGroup.name.chestplate',
        2 => 'itemGroup.name.leggings',
        3 => 'itemGroup.name.boots'
    ];

    private string $textureName;
    private string $armorClass;

    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        ArmorTypeInfo $info,
        string $textureName,
        string $classArmor = 'diamond'
    )
    {
        if (!in_array($classArmor, self::ARMOR_CLASS)) {
            Server::getInstance()->getLogger()->error("[CustomItemAPI] Error //: Item" . $this->getId() . ":" . $this->getMeta()) . ", armor class not found.";
        }
        $this->textureName = $textureName;
        $this->armorClass = $classArmor;
        parent::__construct($identifier, $name, $info);
    }

    public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
        $existing = $player->getArmorInventory()->getItem($this->getArmorSlot());
        $thisCopy = clone $this;
        $new = $thisCopy->pop();
        $player->getArmorInventory()->setItem($this->getArmorSlot(), $new);
        if($thisCopy->getCount() === 0){
            $player->getInventory()->setItemInHand($existing);
        }else{ //if the stack size was bigger than 1 (usually won't happen, but might be caused by plugins
            $player->getInventory()->setItemInHand($thisCopy);
            $player->getInventory()->addItem($existing);
        }
        // TODO: add sound equip.
        return ItemUseResult::SUCCESS();
    }

    public function getComponents(): CompoundTag {
        return CompoundTag::create()
            ->setTag("components", CompoundTag::create()
                ->setTag("minecraft:durability", CompoundTag::create()
                    ->setShort("damage_change", 1)
                    ->setInt("max_durability", $this->getMaxDurability())
                )
                ->setTag("minecraft:armor", CompoundTag::create()
                    ->setString("texture_type", $this->getArmorClass())
                    ->setInt("protection", $this->getDefensePoints())
                )
                ->setTag("minecraft:wearable", CompoundTag::create()
                    ->setInt("slot", $this->getArmorSlot() + 2)
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
                        ->setString("texture", $this->getTextureName())
                        ->setString("legacy_id", "custom:" . $this->name)
                    )
                )
            )
            ->setShort("minecraft:identifier", $this->getId() + ($this->getId() > 0 ? 5000 : -5000))
            ->setTag("minecraft:display_name", CompoundTag::create()
                ->setString("value", $this->checkName($this->getVanillaName()))
            )
            ->setTag("minecraft:on_use", CompoundTag::create()
                ->setByte("on_use", 1)
            )->setTag("minecraft:on_use_on", CompoundTag::create()
                ->setByte("on_use_on", 1)
            );
    }


    public function getTextureName(): string {
       return $this->textureName;
    }

    public function getArmorClass(): string {
        return $this->armorClass;
    }
}