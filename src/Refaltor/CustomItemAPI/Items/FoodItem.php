<?php

namespace Refaltor\CustomItemAPI\Items;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use Refaltor\CustomItemAPI\CustomItemMain;
use Refaltor\CustomItemAPI\Dependency\FoodComponents;
use Refaltor\CustomItemAPI\Events\ItemCreationEvents;

class FoodItem extends Food
{
    private string $texture_path;
    private int $maxStackSize;
    private int $foodRestore;
    private float $saturationRestore;

    private $listenerInteract;
    private $listenerDestroyBlock;
    private $listenerClickAir;
    private $listenerAttackEntity;
    private $listenerConsume;
    protected $lore;

    public function __construct(
        ItemIdentifier $identifier,
        string $name,
        int $foodRestore,
        float $saturationRestore,
        string $texture_path = 'Unknown',
        int $maxStackSize = 64,
        ?callable $listenerInteract = null,
        ?callable $listenerDestroyBlock = null,
        ?callable $listenerClickAir = null,
        ?callable $listenerAttackEntity = null,
        ?callable $listenerConsume = null,
        array $lore = []
    )
    {
        $this->texture_path = $texture_path;
        $this->maxStackSize = $maxStackSize;
        $this->foodRestore = $foodRestore;
        $this->listenerConsume = $listenerConsume;
        $this->saturationRestore = $saturationRestore;
        $this->listenerAttackEntity = $listenerAttackEntity;
        $this->listenerClickAir = $listenerClickAir;
        $this->listenerInteract = $listenerInteract;
        $this->listenerDestroyBlock = $listenerDestroyBlock;
        $this->lore = $lore;
        parent::__construct($identifier, $name);
    }

    public function setInteractListener(callable $callable): void
    {
        $this->listenerInteract = $callable;
    }

    public function setDestroyBlockListener(callable $callable): void
    {
        $this->listenerDestroyBlock = $callable;
    }

    public function setClickAirListener(callable $callable): void
    {
        $this->listenerClickAir = $callable;
    }

    public function setAttackEntityListener(callable $callable): void
    {
        $this->listenerAttackEntity = $callable;
    }

    public function setConsumeListener(callable $callable): void
    {
        $this->listenerConsume = $callable;
    }

    public function onConsume(Living $consumer): void
    {
        if (is_callable($this->listenerConsume)) call_user_func($this->listenerConsume, $consumer, $this);
    }

    public function onAttackEntity(Entity $victim): bool
    {
        if (is_callable($this->listenerAttackEntity)) call_user_func($this->listenerAttackEntity, $victim, $this);
        return parent::onAttackEntity($victim);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        if (is_callable($this->listenerClickAir)) call_user_func($this->listenerClickAir, $player, $directionVector, $this);
        return parent::onClickAir($player, $directionVector);
    }

    public function onDestroyBlock(Block $block): bool
    {
        if (is_callable($this->listenerDestroyBlock)) call_user_func($this->listenerDestroyBlock, $block, $this);
        return parent::onDestroyBlock($block);
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector): ItemUseResult
    {
        if (is_callable($this->listenerInteract)) call_user_func($this->listenerInteract, $player, $blockReplace, $blockClicked, $face, $clickVector, $this);
        return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector);
    }

    public function addToServer(bool $eventCall = true): void
    {
        if ($eventCall) {
            (new ItemCreationEvents($this))->call();
        } else CustomItemMain::getInstance()->items[] = $this;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setMaxStackSize(int $maxStackSize): void
    {
        $this->maxStackSize = $maxStackSize;
    }

    public function setTexturePath(string $texture_path): void
    {
        $this->texture_path = $texture_path;
    }

    public function getComponents(): CompoundTag
    {
        $components = new FoodComponents();
        $components->foodRestore = $this->getFoodRestore();
        $components->display_name = $this->getName();
        $components->id = $this->getId();
        $components->max_stack_size = $this->getMaxStackSize();
        $components->texture_path = $this->getTexturePath();
        return $components->serializeToNbt();
    }

    public function getTexturePath(): string
    {
        return $this->texture_path;
    }

    public function getMaxStackSize(): int
    {
        return $this->maxStackSize;
    }

    public function getFoodRestore(): int
    {
        return $this->foodRestore;
    }

    public function getSaturationRestore(): float
    {
        return $this->saturationRestore;
    }


    public function setSaturationRestore(float $saturationRestore): void
    {
        $this->saturationRestore = $saturationRestore;
    }

    public function setFoodRestore(int $foodRestore): void
    {
        $this->foodRestore = $foodRestore;
    }
}