<?php

namespace Refaltor\CustomItemAPI;

use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\plugin\PluginBase;
use Refaltor\CustomItemAPI\Events\Listeners\PacketListener;
use Refaltor\CustomItemAPI\Events\Listeners\PlayerListener;
use Refaltor\CustomItemAPI\Items\ArmorItem;
use Refaltor\CustomItemAPI\Items\BasicItem;
use Refaltor\CustomItemAPI\Items\StructureItem;
use ReflectionClass;
use ReflectionProperty;

class CustomItemMain extends PluginBase
{

    public ItemComponentPacket $packet;
    protected array $registered = [];
    protected ReflectionProperty $coreToNetMap;
    protected ReflectionProperty $netToCoreMap;
    protected array $coreToNetValues = [];
    protected array $netToCoreValues = [];
    protected ReflectionProperty $itemTypeMap;
    protected array $packetEntries = [];
    protected array $itemTypeEntries = [];

    public array $items = [];
    private static ?self $instance = null;

    protected function onLoad(): void
    {
        if (is_null(self::$instance)) self::$instance = $this;


        $item = new ArmorItem(new ItemIdentifier(1000, 0), 'armor test', new ArmorTypeInfo(100, 100, 0), 'stick');
        $item->addToServer();
    }

    protected function onEnable(): void
    {
        foreach ([new PacketListener(), new PlayerListener()] as $event) $this->getServer()->getPluginManager()->registerEvents($event, $this);
        $ref = new ReflectionClass(ItemTranslator::class);
        $this->coreToNetMap = $ref->getProperty("simpleCoreToNetMapping");
        $this->netToCoreMap = $ref->getProperty("simpleNetToCoreMapping");
        $this->coreToNetMap->setAccessible(true);
        $this->netToCoreMap->setAccessible(true);
        $this->coreToNetValues = $this->coreToNetMap->getValue(ItemTranslator::getInstance());
        $this->netToCoreValues = $this->netToCoreMap->getValue(ItemTranslator::getInstance());
        $ref_1 = new ReflectionClass(ItemTypeDictionary::class);
        $this->itemTypeMap = $ref_1->getProperty("itemTypes");
        $this->itemTypeMap->setAccessible(true);
        $this->itemTypeEntries = $this->itemTypeMap->getValue(GlobalItemTypeDictionary::getInstance()->getDictionary());
        $this->packetEntries = [];
        $items = $this->getItemsInCache();
        foreach ($items as $item) {
            if ($item instanceof StructureItem || $item instanceof ArmorItem) {
                $runtimeId = $item->getId() + ($item->getId() > 0 ? 5000 : -5000);
                $this->coreToNetValues[$item->getId()] = $runtimeId;
                $this->netToCoreValues[$runtimeId] = $item->getId();
                $this->itemTypeEntries[] = new ItemTypeEntry("minecraft:" . $item->getName(), $runtimeId, true);
                $this->packetEntries[] = new ItemComponentPacketEntry("minecraft:" . $item->getName(), new CacheableNbt($item->getComponents()));;
                $this->registered[] = $item;
                $new = clone $item;
                StringToItemParser::getInstance()->register($item->getName(), fn() => $new);
                ItemFactory::getInstance()->register($item, true);
                CreativeInventory::getInstance()->add($item);
                $this->netToCoreMap->setValue(ItemTranslator::getInstance(), $this->netToCoreValues);
                $this->coreToNetMap->setValue(ItemTranslator::getInstance(), $this->coreToNetValues);
                $this->itemTypeMap->setValue(GlobalItemTypeDictionary::getInstance()->getDictionary(), $this->itemTypeEntries);
                $this->packet = ItemComponentPacket::create($this->packetEntries);
            }
        }
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