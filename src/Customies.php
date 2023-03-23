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
                    foreach ($items as $itemName => $values) {
                        $itemName = $values['name'];
                        $textureName = $values['texture_path'];
                        $maxStackSize = $values['max_stack_size'];
                        $allowOffHand = $values['allow_off_hand'] ?? false;
                        $renderOffset = $values['render_offest'];
                        CustomiesItemFactory::getInstance()->registerItem(BasicItem::class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $textureName, $allowOffHand, $maxStackSize, $renderOffset);
                    }
                    break;
                case 'armor_items':
                    foreach ($items as $itemName => $values) {
                        $itemName = $values['name'];
                        $type = $values['type'];
                        $defensePoints = $values['defense_points'];
                        $durability = $values['durability'];
                        $textureName = $values['texture_path'];
                        $renderOffset = $values['render_offest'];

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
                    foreach ($items as $itemName => $values) {
                        $itemName = $values['name'];
                        $textureName = $values['texture_path'];
                        $maxStackSize = $values['max_stack_size'];
                        $isPotion = $values['is_potion'];
                        $canAlwaysEat = $values['can_always_eat'];
                        $foodRestore = $values['food_restore'];
                        $saturationRestore = $values['saturation_restore'];
                        $renderOffset = $values['render_offest'];
                        CustomiesItemFactory::getInstance()->registerFood(FoodItem::class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $isPotion, $textureName, $maxStackSize, $canAlwaysEat, $foodRestore, $saturationRestore, $renderOffset);
                    }
                    break;
                case 'tool_items':
                    foreach ($items as $itemName => $values) {
                        $itemName = $values['name'];
                        $textureName = $values['texture_path'];
                        $type = $values['type'];
                        $tier = $values['tier'];
                        $speedMining = $values['mining_speed'];
                        $durability = $values['durability'];
                        $attack = $values['attack_point'];
                        $renderOffset = $values['render_offest'];

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
                        CustomiesItemFactory::getInstance()->registerFood($class, 'custom:' . strtolower(str_replace(' ', '_', $itemName)), $itemName, $durability, $speedMining, $textureName, $attack, $renderOffset, $tier);
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
