<?php

/**
 * Only support .txt files.
 */

namespace EmreTr1;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command,CommandSender};
use pocketmine\event\Listener;
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;

class LiveTexts extends PluginBase implements Listener{
	
	public $cache = [], $whatid = [];
	public $prefix = "§7» §bLive§3Text §7> ";
	
	public function onEnable(){
		$this->scanTextFiles($this->getDataFolder());
		Entity::registerEntity(Text::class, true);
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	}
	
	public function scanTextFiles(string $dir){
		if(is_dir($dir)){
			$files = scandir($dir);
			
			foreach($files as $file){
				$name = $file;
				$data = trim(str_ireplace("\r\n", "\n", file_get_contents($dir . $file)));
				
				$this->cache[$name] = $data;
			}
		}
	}
	
	public function onCommand(CommandSender $p, Command $cmd, $label, array $args){
if(!$p->isOp()) return false;
		if(!empty($args[0])){
			switch($args[0]){
				case 'addtext':
				    array_shift($args);
				    $text = implode(" ", $args);
				    $this->addLiveText($p, $text);
				    $p->sendMessage($this->prefix."§aLiveText created(without file)");
				    break;
				case 'add':
				    if(!empty($args[1])){
				    	$name = $args[1];
				    	if(isset($this->cache[$name])){
				    		$text = $this->cache[$name];
				    		$this->addLiveText($p, $text);
				    		$p->sendMessage($this->prefix. "§aLiveText created(with file)");
				    	}else{
				    		$p->sendMessage($this->prefix."§7{$name} file not found!");
				    	}
				    }else{
				    	$p->sendMessage($this->prefix."§7/lt add <filename>");
				    }
				    break;
				case 'id':
				    $this->whatid[$p->getName()] = true;
				    $p->sendMessage($this->prefix."§eTap a entity for known id");
				    break;
				case 'remove':
				    if(!empty($args[1])){
				    	$id = $args[1];
				    	foreach($p->level->getEntities() as $e){
				    		if($e->getId() == $id){
				    			$e->close();
				    		}
				    	}
				    	$p->sendMessage($this->prefix."§aText removed.");
				    }
			}
		}
	}
	
	public function onDamage(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			$p = $event->getDamager();
			$entity = $event->getEntity();
			if(isset($this->whatid[$p->getName()])){
				$id = $entity->getId();
				$p->sendMessage($this->prefix."§aEntity ID: {$id}");
				unset($this->whatid[$p->getName()]);
			}
		}
	}
	
	public function addLiveText($pos, string $text){
	$nbt = new CompoundTag;
	$nbt->Pos = new ListTag("Pos", [new DoubleTag("", $pos->x), new DoubleTag("", $pos->y), new DoubleTag("", $pos->z)]);
	$nbt->Rotation = new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]);
	$nbt->Motion = new ListTag("Pos", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]);
   $nbt->baseText = new StringTag("baseText", $text);

	$entity = Entity::createEntity("Text", $pos->level, $nbt);
	$entity->setNameTag($text);
	$entity->spawnToAll();
	}
	
	public static function replacedText(string $text){ 
		$server = Server::getInstance();
		$tps=$server->getTicksPerSecond();
		$onlines=count($server->getOnlinePlayers());
		$maxplayers=$server->getMaxPlayers();
		$worldsc=count($server->getLevels());
		$variables=[
		"{line}"=>"\n",
		"{tps}"=>$tps,
		"{maxplayers}"=>$maxplayers,
		"{onlines}"=>$onlines,
		"{worldscount}"=>$worldsc,
		"{ip}"=>$server->getIp(),
		"{port}"=>$server->getPort(),
		"{motd}"=>$server->getMotd(),
		"{network}"=>$server->getNetwork()->getName()];
		
		foreach($variables as $var=>$ms){
			$text=str_ireplace($var, $ms, $text);
		}
		return $text;
	}
   	
	public static function replaceForPlayer(Player $p, string $text){
		$specialvars = [
		"{name}"=>$p->getName(),
		"{nametag}"=>$p->getNameTag(),
		"{hunger}"=>$p->getFood(),
		"{health}"=>$p->getHealth(),
		"{maxhealth}"=>$p->getMaxHealth(),
		"{nbt}"=>$p->namedtag,
		"{level}"=>$p->getLevel()->getFolderName()];
		
		foreach($specialvars as $var=>$ms){
			$text = str_ireplace($var, $ms, $text);
		}
		return $text;
	}
	
}
?>
