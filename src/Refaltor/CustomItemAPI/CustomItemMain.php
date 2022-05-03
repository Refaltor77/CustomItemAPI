<?php

declare(strict_types = 1);

namespace Refaltor\CustomItemAPI;

use pocketmine\crafting\FurnaceRecipe;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\StringToItemParser;
use pocketmine\item\ToolTier;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Refaltor\CustomItemAPI\Events\Listeners\EntityListener;
use Refaltor\CustomItemAPI\Events\Listeners\ItemCreationEventExample;
use Refaltor\CustomItemAPI\Events\Listeners\PacketListener;
use Refaltor\CustomItemAPI\Events\Listeners\PlayerListener;
use Refaltor\CustomItemAPI\Items\ArmorItem;
use Refaltor\CustomItemAPI\Items\AxeItem;
use Refaltor\CustomItemAPI\Items\BasicItem;
use Refaltor\CustomItemAPI\Items\FoodItem;
use Refaltor\CustomItemAPI\Items\HoeItem;
use Refaltor\CustomItemAPI\Items\PickaxeItem;
use Refaltor\CustomItemAPI\Items\ShovelItem;
use Refaltor\CustomItemAPI\Items\StructureItem;
use Refaltor\CustomItemAPI\Items\SwordItem;
use Refaltor\CustomItemAPI\Items\CustomItem;
use \Closure;
use Webmozart\PathUtil\Path;
use const pocketmine\BEDROCK_DATA_PATH;

class CustomItemMain extends PluginBase
{

    const BASE_VERSION = '2.6.3';

    public ?ItemComponentPacket $packet = null;
    protected array $registered = [];
    protected array $packetEntries = [];
    private ?Config $settings = null;

    public array $items = [];
    private static ?self $instance = null;

    private function loadConfigurationFile(): void
    {
        $array = $this->getConfig()->getAll();
        foreach ($array['items'] as $name => $values)
        {
            $type = strtolower($values['type']);
            if ($type === 'basic') {
                $name = strval($values['name']);
                $id = intval($values['id']);
                $meta = intval($values['meta']);
                $max_stack_size = intval($values['max_stack_size']);
                $texture_path = strval($values['texture_path']);
                (new BasicItem(new ItemIdentifier($id, $meta), $name, $texture_path, $max_stack_size))->addToServer();
            } elseif ($type === 'armor') {
                $name = strval($values['name']);
                $id = intval($values['id']);
                $meta = intval($values['meta']);
                $texture_path = strval($values['texture_path']);
                $armorGroup = strtolower(strval($values['armor_group']));
                $defense_points = intval($values['defense_points']);
                $max_durability = intval($values['max_durability']);

                $item = match ($armorGroup) {
                    'helmet' => new ArmorItem(new ItemIdentifier($id, $meta), $name, new ArmorTypeInfo($defense_points, $max_durability, 0), $texture_path),
                    'chestplate' => new ArmorItem(new ItemIdentifier($id, $meta), $name, new ArmorTypeInfo($defense_points, $max_durability, 1), $texture_path),
                    'leggings' => new ArmorItem(new ItemIdentifier($id, $meta), $name, new ArmorTypeInfo($defense_points, $max_durability, 2), $texture_path),
                    'boots' => new ArmorItem(new ItemIdentifier($id, $meta), $name, new ArmorTypeInfo($defense_points, $max_durability, 3), $texture_path),
                };

                $item->addToServer();
            } elseif($type === 'food') {
                $name = strval($values['name']);
                $id = intval($values['id']);
                $meta = intval($values['meta']);
                $max_stack_size = intval($values['max_stack_size']);
                $texture_path = strval($values['texture_path']);
                $food_restore = intval($values['food_restore']);
                $saturation_restore = floatval($values['saturation_restore']);
                (new FoodItem(new ItemIdentifier($id, $meta), $name, $food_restore, $saturation_restore, $texture_path, $max_stack_size))->addToServer();
            } elseif($type === 'tool') {
                $name = strval($values['name']);
                $id = intval($values['id']);
                $meta = intval($values['meta']);
                $texture_path = strval($values['texture_path']);
                $tier = match (strval($values['tier'])) {
                    'diamond' => ToolTier::DIAMOND(),
                    'gold' => ToolTier::GOLD(),
                    'iron' => ToolTier::IRON(),
                    'stone' => ToolTier::STONE(),
                    'wood' => ToolTier::WOOD()
                };

                $item = match (strtolower(strval($values['tool_group']))) {
                    'pickaxe' => new PickaxeItem(new ItemIdentifier($id, $meta), $name, $tier, floatval($values['mining_efficiency']), intval($values['max_durability']), $texture_path),
                    'sword' => new SwordItem(new ItemIdentifier($id, $meta), $name, $tier, intval($values['max_durability']), intval($values['attack_points']), $texture_path),
                    'axe' => new AxeItem(new ItemIdentifier($id, $meta), $name, $tier, floatval($values['mining_efficiency']),$values['max_durability'], $texture_path),
                    'shovel' => new ShovelItem(new ItemIdentifier($id, $meta), $name, $tier, intval($values['max_durability']), floatval($values['mining_efficiency']),$texture_path),
                    'hoe' => new HoeItem(new ItemIdentifier($id, $meta), $name, $tier, intval($values['max_durability']), $texture_path),
                };

                $item->addToServer();
            } else {
                //NOPE
            }
        }
    }

    public function createSwordItem(ItemIdentifier $identifier, string $name, ToolTier $tier, int $durability, int $attackPoints, string $texturePath): SwordItem {
        return new SwordItem($identifier, $name, $tier, $durability, $attackPoints, $texturePath);
    }

    public function createArmorItem(ItemIdentifier $identifier, string $name, ArmorTypeInfo $info, string $texture_path): ArmorItem {
        return new ArmorItem($identifier, $name, $info, $texture_path);
    }

    public function createFoodItem(ItemIdentifier $identifier, string $name, int $foodRestore,float $saturationRestore , string $texture_path, int $maxStackSize): FoodItem {
        return new FoodItem($identifier, $name, $foodRestore, $saturationRestore, $texture_path, $maxStackSize);
    }

    public function createBasicItem(ItemIdentifier $identifier, string $name, int $maxStackSize, string $texture_path): BasicItem {
        return new BasicItem($identifier, $name, $texture_path, $maxStackSize);
    }

    public function createPickaxeItem(ItemIdentifier $identifier, string $name, ToolTier $tier,  int $miningEfficiency, int $maxDurability, string $texture_path): PickaxeItem {
        return new PickaxeItem($identifier, $name, $tier, $miningEfficiency, $maxDurability, $texture_path);
    }

    public function createAxeItem(ItemIdentifier $identifier, string $name, ToolTier $tier,  int $miningEfficiency, int $maxDurability, string $texture_path): AxeItem {
        return new AxeItem($identifier, $name, $tier, $miningEfficiency, $maxDurability, $texture_path);
    }

    public function createShovelItem(ItemIdentifier $identifier, string $name, ToolTier $tier,  int $miningEfficiency, int $maxDurability, string $texture_path): ShovelItem {
        return new ShovelItem($identifier, $name, $tier, $maxDurability, $miningEfficiency, $texture_path);
    }

    public function createHoeItem(ItemIdentifier $identifier, string $name, ToolTier $tier, int $maxDurability, string $texture_path): HoeItem {
        return new HoeItem($identifier, $name, $tier, $maxDurability, $texture_path);
    }

    public function register(CustomItem $item) {
        try {
            $item->addToServer();
        } catch (\Exception $exception) {
            $this->getLogger()->error("[!] ". $item::class ." Is not custom item.");
        }
    }

    public function registerAll(array $items) {
        foreach ($items as $item) {
            try {
                $item->addToServer();
            } catch (\Exception $exception) {
                $this->getLogger()->error("[!] ". $item::class ." Is not custom item.");
            }
        }
    }

    public function getUpdatingInventory(): int {
        return $this->settings->get('update_creative_inventory') ?? 40;
    }

    protected function onLoad(): void
    {
        $this->saveDefaultConfig();
        $this->saveResource('settings.yml');

        $settings = new Config($this->getDataFolder() . 'settings.yml', Config::YAML);
        if ($settings->get('version') !== self::BASE_VERSION) {
            $this->saveResource('settings.yml', true);
        }
        $this->settings = $settings;
        if (is_null(self::$instance)) self::$instance = $this;
        @mkdir($this->getDataFolder() . 'debugs/');
        //@mkdir($this->getDataFolder() . 'Temps/');
        //@mkdir($this->getDataFolder() . 'resourcesTemps/');
        $this->loadConfigurationFile();
    }

    protected function onEnable(): void
    {
        $t = microtime(true);
        foreach ([new PacketListener($this), new PlayerListener(), new ItemCreationEventExample($this), new EntityListener()] as $event)
        {
            $this->getServer()->getPluginManager()->registerEvents($event, $this);
        }
        $this->packetEntries = [];
        $items = $this->getItemsInCache();
        CreativeInventory::getInstance()->clear();

        $translator = ItemTranslator::getInstance();
        $typeDictionary = GlobalItemTypeDictionary::getInstance()->getDictionary();
        foreach ($items as $item) {
            if ($item instanceof CustomItem && $item instanceof Item) {
                $legacyId = $item->getId();
                $legacyMeta = $item->getMeta();
                $runtimeId = $legacyId + ($legacyId > 0 ? 5000 : -5000);
                $name = $item->getName();
                $stringId = 'custom:' . $name;
                
                $this->packetEntries[] = new ItemComponentPacketEntry($stringId, new CacheableNbt($item->getComponents()));;
                $this->registered[] = $item;
                $new = clone $item;
                StringToItemParser::getInstance()->register($name, fn() => $new);
                ItemFactory::getInstance()->register($item, true);
                CreativeInventory::getInstance()->add($item);

                Closure::bind( //HACK: Closure bind hack to access inaccessible members
                    static function(ItemTranslator $translator) use ($runtimeId, $legacyId, $legacyMeta): void
                    {
                        if($legacyMeta === -1) {
                            $translator->simpleCoreToNetMapping[$legacyId] = $runtimeId;
                            $translator->simpleNetToCoreMapping[$runtimeId] = $legacyId;
                        } else {
                            $translator->complexCoreToNetMapping[$legacyId][$legacyMeta] = $runtimeId;
                            $translator->complexNetToCoreMapping[$runtimeId] = [$legacyId, $legacyMeta];
                        }
                    },
                    null,
                    ItemTranslator::class
                )($translator);

                Closure::bind( //HACK: Closure bind hack to access inaccessible members
                    static function(ItemTypeDictionary $dictionary) use ($stringId, $runtimeId): void
                    {
                        $dictionary->itemTypes[] = new ItemTypeEntry($stringId, $runtimeId, true);
                        $dictionary->stringToIntMap[$stringId] = $runtimeId;
                        $dictionary->intToStringIdMap[$runtimeId] = $stringId;
                    },
                    null,
                    ItemTypeDictionary::class
                )($typeDictionary);
            }
        }
        $this->packet = ItemComponentPacket::create($this->packetEntries);


        $creativeItems = json_decode(file_get_contents(Path::join(BEDROCK_DATA_PATH, "creativeitems.json")), true);
        foreach($creativeItems as $data){
            $item = Item::jsonDeserialize($data);
            if($item->getName() === "Unknown"){
                continue;
            }
            CreativeInventory::getInstance()->add($item);
        }
        echo(microtime(true)-$t . "sec\n");
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    private function getItemsInCache(): array
    {
        return $this->items;
    }
}
