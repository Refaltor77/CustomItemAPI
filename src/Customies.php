<?php
declare(strict_types=1);

namespace customiesdevs\customies;

use Closure;
use core\items\armors\tungstene\TungsteneHelmet;
use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\types\AxeItem;
use customiesdevs\customies\item\types\BasicItem;
use customiesdevs\customies\item\types\CustomArmor;
use customiesdevs\customies\item\types\FoodItem;
use customiesdevs\customies\item\types\HoeItem;
use customiesdevs\customies\item\types\PickaxeItem;
use customiesdevs\customies\item\types\ShovelItem;
use customiesdevs\customies\item\types\SwordItem;
use customiesdevs\customies\util\Cache;
use customiesdevs\customies\world\LevelDB;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\format\io\WritableWorldProviderManagerEntry;

final class Customies extends PluginBase {

    protected function onLoad(): void {
        Cache::setInstance(new Cache($this->getDataFolder() . "idcache"));
        $provider = new WritableWorldProviderManagerEntry(\Closure::fromCallable([LevelDB::class, 'isValid']), fn(string $path) => new LevelDB($path), Closure::fromCallable([LevelDB::class, 'generate']));
        $this->getServer()->getWorldManager()->getProviderManager()->addProvider($provider, "leveldb", true);
        $this->getServer()->getWorldManager()->getProviderManager()->setDefault($provider);
    }

    protected function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents(new CustomiesListener(), $this);
        $this->saveDefaultConfig();

        $allItemsConfig = $this->getConfig()->getAll();

        foreach ($allItemsConfig as $itemType => $items) {
            switch ($itemType) {
                case 'basic_items':
                    foreach ($items as $itemName => $valuesBasic) {
                        $itemName = $valuesBasic['name'];
                        $textureName = $valuesBasic['texture_path'];
                        $maxStackSize = $valuesBasic['max_stack_size'];
                        $allowOffHand = $valuesBasic['allow_off_hand'] ?? false;
                        $renderOffset = $valuesBasic['render_offset'];
                        CustomiesItemFactory::getInstance()->registerItem(BasicItem::class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $textureName, $maxStackSize, $allowOffHand, $renderOffset);
                    }
                    break;
                case 'armor_items':
                    foreach ($items as $itemName => $valuesArmor) {
                        $itemName = $valuesArmor['name'];
                        $type = $valuesArmor['type'];
                        $defensePoints = $valuesArmor['defense_points'];
                        $durability = $valuesArmor['durability'];
                        $textureName = $valuesArmor['texture_path'];
                        $renderOffset = $valuesArmor['render_offset'];

                        switch ($type) {
                            case "helmet":
                                $slot = ArmorInventory::SLOT_HEAD;
                                break;
                            case "chestplate":
                                $slot = ArmorInventory::SLOT_CHEST;
                                break;
                            case "leggings":
                                $slot = ArmorInventory::SLOT_LEGS;
                                break;
                            case "boots":
                                $slot = ArmorInventory::SLOT_FEET;
                                break;
                        }


                        $info = new ArmorTypeInfo($defensePoints, $durability, $slot);
                        CustomiesItemFactory::getInstance()->registerArmor(CustomArmor::class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $info, $textureName, $renderOffset);
                    }
                    break;
                case 'food_items':
                    foreach ($items as $itemName => $valuesFood) {
                        $itemName = (string)$valuesFood['name'];
                        $textureName = (string)$valuesFood['texture_path'];
                        $maxStackSize = (int)$valuesFood['max_stack_size'];
                        $isPotion = (bool)$valuesFood['is_potion'];
                        $canAlwaysEat = (bool)$valuesFood['can_always_eat'];
                        $foodRestore = (int)$valuesFood['food_restore'];
                        $saturationRestore = (float)$valuesFood['saturation_restore'];
                        $renderOffset = (int)$valuesFood['render_offset'];
                        CustomiesItemFactory::getInstance()->registerFood(FoodItem::class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $isPotion, $textureName, $maxStackSize, $canAlwaysEat, $foodRestore, $saturationRestore, $renderOffset);
                    }
                    break;
                case 'tool_items':
                    foreach ($items as $itemName => $valuesTools) {
                        $itemName = $valuesTools['name'];
                        $textureName = $valuesTools['texture_path'];
                        $type = $valuesTools['type'];
                        $tier = $valuesTools['tier'];
                        $speedMining = $valuesTools['mining_speed'];
                        $durability = $valuesTools['durability'];
                        $attack = $valuesTools['attack_point'];
                        $renderOffset = $valuesTools['render_offset'];

                        switch ($tier) {
                            case 'diamond':
                            default:
                                $tier = ToolTier::DIAMOND();
                                break;
                            case 'golden':
                                $tier = ToolTier::GOLD();
                                break;
                            case 'iron':
                                $tier = ToolTier::IRON();
                                break;
                            case 'stone':
                                $tier = ToolTier::STONE();
                                break;
                            case 'wooden':
                                $tier = ToolTier::WOOD();
                                break;
                        }

                        switch ($type) {
                            case 'pickaxe':
                                $class = PickaxeItem::class;
                                break;
                            case 'axe':
                                $class = AxeItem::class;
                                break;
                            case 'shovel':
                                $class = ShovelItem::class;
                                break;
                            case 'sword':
                                $class = SwordItem::class;
                                break;
                            case 'hoe':
                                $class = HoeItem::class;
                                break;
                        }
                        CustomiesItemFactory::getInstance()->registerTool($class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $durability, $speedMining, $textureName, $attack, $renderOffset, $tier);
                    }
                    break;
            }
        }

        $cachePath = $this->getDataFolder() . "idcache";
        $this->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($cachePath): void {
            // This task is scheduled with a 0-tick delay so it runs as soon as the server has started. Plugins should
            // register their custom blocks and entities in onEnable() before this is executed.
            Cache::getInstance()->save();
            CustomiesBlockFactory::getInstance()->registerCustomRuntimeMappings();
            CustomiesBlockFactory::getInstance()->addWorkerInitHook($cachePath);
        }), 0);
    }
}
