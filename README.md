# Welcome to CustomItemAPI

> The plugin allows you to add as many items as you want
> on your server, you can add new foods, 
> New pickaxes and much more!
> A configuration is present for people
> not having the necessary development skills.

---
## Getting started with the API

To start development with the API, you need to have some conditions.
``plugin.yml``
```YAML
name: TestCustomItem
version: 1.0.0
author: you
main: you\test\Main
api: 4.0.0
depend: CustomItemAPI # it is very important !!!
```
Now that our plugin depends on CustomItemAPI, we can start by creating a basic item.

```PHP
use pocketmine\item\ItemIdentifier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\BaseItem;


$item = new BaseItem(new ItemIdentifier(1000, 0), 'test', 'test_texture', 64, false);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onLoad().
```

## Armor Creation

```PHP
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomArmor;


$item = new CustomArmor(new ItemIdentifier(1000, 0), 'armor_test', new ArmorTypeInfo(5, 100, ArmorInventory::SLOT_HEAD), 'test_texture');
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Food Creation

```PHP
use pocketmine\item\ItemIdentifier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomFood;

$item = new CustomFood(new ItemIdentifier(1000, 0), 'test_food', 'texture_name', false, 5, 10.00, 64);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Potion Creation

```PHP
use pocketmine\item\ItemIdentifier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomPotion;

$item = new CustomPotion(new ItemIdentifier(1000, 0), 'test_potion', 'texture_name', true, 5, 10.00, 64); # the animation of the eating will be a potion
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```

## Pickaxe Creation

```PHP
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomPickaxe;

$item = new CustomPickaxe(new ItemIdentifier(1000, 0), 'test_pickaxe', ToolTier::DIAMOND(), 'texture_path', 4.5, 455, 2);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Shovel Creation

```PHP
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomShovel;

$item = new CustomShovel(new ItemIdentifier(1000, 0), 'test_shovel', ToolTier::DIAMOND(), 'texture_path', 4.5, 455, 2);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Axe Creation

```PHP
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomAxe;

$item = new CustomAxe(new ItemIdentifier(1000, 0), 'test_axe', ToolTier::DIAMOND(), 'texture_path', 4.5, 455, 2);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Hoe Creation

```PHP
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomHoe;

$item = new CustomHoe(new ItemIdentifier(1000, 0), 'test_hoe', ToolTier::DIAMOND(), 'texture_path', 455, 2);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```


## Sword Creation

```PHP
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use refaltor\customitemapi\CustomItemAPI;
use refaltor\customitemapi\items\CustomSword;

$item = new CustomSword(new ItemIdentifier(1000, 0), 'test_sword', ToolTier::DIAMOND(), 'texture_path', 455, 2);
CustomItemAPI::getInstance()->getAPI()->register($item); # the item is set to hold fill and will be register when onEnable().
```
