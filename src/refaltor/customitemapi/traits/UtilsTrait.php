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

namespace refaltor\customitemapi\traits;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\items\BaseItem;
use refaltor\customitemapi\items\CustomArmor;
use refaltor\customitemapi\items\CustomAxe;
use refaltor\customitemapi\items\CustomFood;
use refaltor\customitemapi\items\CustomHoe;
use refaltor\customitemapi\items\CustomPickaxe;
use refaltor\customitemapi\items\CustomPotion;
use refaltor\customitemapi\items\CustomShovel;
use refaltor\customitemapi\items\CustomSword;

trait UtilsTrait
{
    public function getRuntimeId(int $id): int {
        return $id + ($id > 0 ? 5000 : -5000);
    }

    public function checkName(string $name): string {
        $name = strtolower($name);
        $str = preg_replace('/\s+/', '-', $name);
        return "item." . $str . ".name";
    }

    public function checkItem(Item $item): bool {
        if (in_array($item::class, [
            CustomPickaxe::class
        ])) return true;
        return false;
    }

    public function loadConfigurationFiles(): void {
        $this->getServer()->getLogger()->debug("[CustomItemAPI] Logs //: parsing configuration files...");
        $arrayQueried = [];
        $config = $this->getConfig()->getAll();
        if (isset($config['basic_items'])) {
            foreach ($config['basic_items'] as $name => $values) {
                $name = $values['name'] ?? $name;
                if (!isset($values['id'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: Id for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
                if (!isset($values['texture_path'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: texture_path for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                $id = $values['id'];
                $meta = $values['meta'] ?? 0;
                $texture_path = $values['texture_path'];
                $max_stack_size = $values['max_stack_size'] ?? 64;
                $allow_off_hand = $values['allow_off_hand'] ?? false;
                $item = new BaseItem(new ItemIdentifier($id, $meta), $name, $texture_path, $max_stack_size, $allow_off_hand);
                $arrayQueried[] = $item;
            }
        }
        if (isset($config['food_items'])) {
            foreach ($config['food_items'] as $name => $values) {
                $name = $values['name'] ?? $name;
                if (!isset($values['id'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: Id for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
                if (!isset($values['texture_path'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: texture_path for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['food_restore'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: food_restore for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['saturation_restore'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: saturation_restore for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                $id = $values['id'];
                $meta = $values['meta'] ?? 0;
                $texture_path = $values['texture_path'];
                $max_stack_size = $values['max_stack_size'] ?? 64;
                $saturation_restore = $values['saturation_restore'];
                $food_restore = $values['food_restore'];
                $is_potion = $values['is_potion'] ?? false;
                $can_always_eat = $values['can_always_eat'] ?? false;
                if ($is_potion) {
                    $item = new CustomPotion(new ItemIdentifier($id, $meta), $name, $texture_path, $can_always_eat, $food_restore, $saturation_restore, $max_stack_size);
                } else $item = new CustomFood(new ItemIdentifier($id, $meta), $name, $texture_path, $can_always_eat, $food_restore, $saturation_restore, $max_stack_size);
                $arrayQueried[] = $item;
            }
        }
        if (isset($config['tool_items'])) {
            foreach ($config['tool_items'] as $name => $values) {
                $name = $values['name'] ?? $name;
                if (!isset($values['id'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: Id for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
                if (!isset($values['texture_path'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: texture_path for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['durability'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: durability for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['attack_point'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: attack_point for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['mining_speed'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: mining_speed for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['tier'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: tier for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['type'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: type for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!in_array($values['type'], ['pickaxe', 'axe', 'sword', 'shovel', 'hoe'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: type unknown for item " . $name);
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!in_array($values['tier'], ['diamond', 'golden', 'iron', 'stone', 'wooden'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: tier unknown for item " . $name);
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                $id = $values['id'];
                $meta = $values['meta'] ?? 0;
                $texture_path = $values['texture_path'];
                $attack_point = $values['attack_point'];
                $mining_speed = $values['mining_speed'];
                $durability = $values['durability'] ?? false;
                $tier = match (strtolower($values['tier'])) {
                    'diamond' => ToolTier::DIAMOND(),
                    'golden' => ToolTier::GOLD(),
                    'iron' => ToolTier::IRON(),
                    'stone' => ToolTier::STONE(),
                    'wooden' => ToolTier::WOOD()
                };
                $item = match (strtolower($values['type'])) {
                    'pickaxe' => new CustomPickaxe(new ItemIdentifier($id, $meta), $name, $tier, $texture_path, $mining_speed, $durability, $attack_point),
                    'shovel' => new CustomShovel(new ItemIdentifier($id, $meta), $name, $tier, $texture_path, $mining_speed, $durability, $attack_point),
                    'axe' => new CustomAxe(new ItemIdentifier($id, $meta), $name, $tier, $texture_path, $mining_speed, $durability, $attack_point),
                    'hoe' => new CustomHoe(new ItemIdentifier($id, $meta), $name, $tier, $texture_path, $durability, $attack_point),
                    'sword' => new CustomSword(new ItemIdentifier($id, $meta), $name, $tier, $texture_path, $durability, $attack_point),
                };
                $arrayQueried[] = $item;
            }
        }
        if (isset($config['armor_items'])) {
            foreach ($config['armor_items'] as $name => $values) {
                $name = $values['name'] ?? $name;
                if (!isset($values['id'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: Id for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }
                if (!isset($values['texture_path'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: texture_path for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['durability'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: durability for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!isset($values['defense_points'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: defense_points for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }


                if (!isset($values['type'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: type for item " . $name . ' is undefined.');
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                if (!in_array($values['type'], ['helmet', 'chestplate', 'leggings', 'boots'])) {
                    $this->getServer()->getLogger()->error("[CustomItemAPI] Error[CONFIG] //: type unknown for item " . $name);
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                }

                $id = $values['id'];
                $meta = $values['meta'] ?? 0;
                $texture_path = $values['texture_path'];
                $defense = $values['defense_points'];
                $durability = $values['durability'] ?? false;
                $slot = match (strtolower($values['type'])) {
                    'helmet' => ArmorInventory::SLOT_HEAD,
                    'chestplate' => ArmorInventory::SLOT_CHEST,
                    'leggings' => ArmorInventory::SLOT_FEET,
                    'boots' => ArmorInventory::SLOT_LEGS,
                };
                $item = new CustomArmor(new ItemIdentifier($id, $meta), $name, new ArmorTypeInfo($defense, $durability, $slot), $texture_path);
                $arrayQueried[] = $item;
            }
        }
        $this->getAPI()->registerAll($arrayQueried);
        $this->getServer()->getLogger()->debug("[CustomItemAPI] Logs //: configuration files is loaded.");
    }
}