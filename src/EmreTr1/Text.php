<?php

   //Version of LiveTexts 1.4pre
   
   namespace LiveTexts;
   
   use pocketmine\Player;
   use pocketmine\item\Item;
   use pocketmine\math\Vector3;
   use pocketmine\utils\UUID;
   use pocketmine\entity\Entity;
   use pocketmine\nbt\tag\CompoundTag;
   use pocketmine\level\format\FullChunk;
   use pocketmine\event\entity\EntityDamageEvent;
   use pocketmine\network\protocol\AddPlayerPacket;
   
   class Text extends Human{
   	
   	public $text;
   	
   	public function __construct(FullChunk $chunk, CompoundTag $nbt){
	   	parent::__construct($chunk, $nbt);
		  $this->text = $nbt->CustomName;
	  }
   	
   	public function attack($damage, EntityDamageEvent $source){
   		$source->setCancelled(true);
   		parent::attack($damage, $source);
   	}
   	
   	public function onUpdate($currentTick){
   		if($this->closed){
   			return false;
   		}
   		if(!isset($this->namedtag->infos)){
   			return false;
   		}
   		$info=$this->namedtag->infos;
   		$file=$info["file"];
   		if($file!=""){
   		 $data=$info["datafolder"];
   		 $tag = file_get_contents($data); // fixed "ō" bug !
   		 $text=LiveTexts::getInstance()->replacedText($tag);
   		 $this->setNameTag($text);
   		}else{
   			$tag=$this->namedtag->NameTag; // fixed update bug !
   			$text=LiveTexts::getInstance()->replacedText($tag);
   			$this->setNameTag($text);
   		}
   	}
   	
   	public function spawnTo(Player $p){
   		$main=LiveTexts::getInstance();
    $pk = new AddPlayerPacket();
    $pk->eid = $this->getId();
    $pk->uuid = UUID::fromRandom();
    $pk->x = $this->x;
    $pk->y = $this->y - 1.62;
    $pk->z = $this->z;
    $pk->speedX = 0;
    $pk->speedY = 0;
    $pk->speedZ = 0;
    $pk->yaw = 0;
    $pk->pitch = 0;
    $pk->item = Item::get(0);
    $pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_BYTE, 1 <<    Entity::DATA_FLAG_INVISIBLE],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $main->replaceForPlayer($p, $this->text)],
				Entity::DATA_SHOW_NAMETAG => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_NO_AI => [Entity::DATA_TYPE_BYTE, 1],
				Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
				Entity::DATA_LEAD => [Entity::DATA_TYPE_BYTE, 0]
       ];
    $p->dataPacket($pk);
    parent::spawnTo($p);
   	}
   }
?>