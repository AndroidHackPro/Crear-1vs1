<?php
namespace GoldPotatoBlaze;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\scheduler\PluginTask;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\entity\Effect;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\PlayerInventory;
class XvsY extends PluginBase implements Listener{
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0744, true);
		}
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,
			array(
				"受付に使用するブロックID"=>"129",
				"XvsY"=>"2vs2",
				"テレポート地点"=>array("プレイヤー1"=>array("x"=>128,"y"=>4,"z"=>128,"ワールド"=>"world"),"プレイヤー2"=>array("x"=>128,"y"=>4,"z"=>128,"ワールド"=>"world"),"プレイヤー3"=>array("x"=>128,"y"=>4,"z"=>128,"ワールド"=>"world"),"プレイヤー4"=>array("x"=>128,"y"=>4,"z"=>128,"ワールド"=>"world")),
				"体力"=>"20",
				"最大体力"=>"20",
				"制限時間"=>"120",
				"アイテム、エフェクト消去"=>"on",
				"配布アイテム"=>array("アイテム1(ID:メタ:個数)"=>"0:0:0","アイテム2(ID:メタ:個数)"=>"0:0:0"),
				"装備"=>array("ヘルメット"=>0,"チェストプレート"=>0,"レギンス"=>0,"ブーツ"=>0),
				"エフェクト"=>array("エフェクト1(ID:秒数:強さ)"=>"0:0:0","エフェクト2(ID:秒数:強さ)"=>"0:0:0"),
				));
		$this->system["GamesRun"] = "off";
		$this->system["MenberTima"] = "";
		$this->system["Teams"] = "";
		$this->system["Teams2"] = "";
		$this->system["Motion"] = "off";
		$vsvs = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function XvsYTouch(PlayerInteractEvent $event){
		if($event->getBlock()->getId() == $this->config->get("受付に使用するブロックID")){
			$vs = explode("vs",$this->config->get("XvsY"));
			$vs1 = "";
			for($i = 0;$i < count($vs);$i++){
				$vs1 = $vs1 + $vs[$i];
			}
			if($this->system["GamesRun"] == "on"){
				$event->getPlayer()->sendMessage("§d[".$this->config->get("XvsY")."] §c満員です");
			}else{
				$member = explode(":",$this->system["MenberTima"]);
				if(strpos($this->system["MenberTima"],":")){
					$member = explode(":",$this->system["MenberTima"]);
					$a = $member[count($member) - 1] == $event->getPlayer()->getName();
				}else{
					$a = strpos($this->system["MenberTima"],$event->getPlayer()->getName());
				}
				if(strpos($this->system["MenberTima"],":".$event->getPlayer()->getName().":")||$a||$member[0] == $event->getPlayer()->getName()){
					$event->getPlayer()->sendMessage("§d[".$this->config->get("XvsY")."] §c既に受付が完了しています");
				}else{
					if($this->system["MenberTima"] != ""){
						$this->system["MenberTima"] = $this->system["MenberTima"].":".$event->getPlayer()->getName();
					}else{
						$this->system["MenberTima"] = $event->getPlayer()->getName();
					}
					unset($player);
					if(count(explode(":",$this->system["MenberTima"])) == $vs1){
						$down = 3;
						$member1 = explode(":",$this->system["MenberTima"]);
						$player = array($this->getServer()->getPlayer($member1[0]));
						for($i1 = 1; $i1 < $vs1 ;$i1++){
							$player1 = $this->getServer()->getPlayer($member1[$i1]);
							array_push($player,$player1);
						}
						$i1 = 0;
						$i2 = 0;
						$i3 = 0;
						unset($a);
						for($i = 0;$i < $vs1;$i++){
							if($vs[$i1] > $i2){
								$a = $this->system["Teams"];
								$a[$member1[$i3]] = $i1;
								$this->system["Teams"][$member1[$i3]] = $i1;
								$this->system["Teams2"][$member1[$i3]] = $i1;
								$i3++;
								$i2++;
							}else{
								$i1++;
								$i--;
								$i2=0;
							}
						}
						for ( $i = 0; $i < 4 ; $i++){
							$task = new XvsYCountDown($this,$player,$down,$this->system,$this->getServer(),$this->config);
							$this->getServer()->getScheduler()->scheduleDelayedTask($task,$i*20);
							$down--;
						}
						$i = 1;
						foreach($player as $playertp){
							$this->getServer()->loadLevel($this->config->getAll()["テレポート地点"]["プレイヤー".$i]["ワールド"]);
							$level = Server::getInstance()->getLevelByName($this->config->getAll()["テレポート地点"]["プレイヤー".$i]["ワールド"]);
							$xyz1 = new Position($this->config->getAll()["テレポート地点"]["プレイヤー".$i]["x"],$this->config->getAll()["テレポート地点"]["プレイヤー".$i]["y"],$this->config->getAll()["テレポート地点"]["プレイヤー".$i]["z"],$level);
							$playertp->teleport($xyz1);
							$i++;
						}
						$this->system["Motion"] = "on";
						foreach($member1 as $name){
							if(isset($message)){
								if($name1 ==  $this->system["Teams"][$name]){
									$message = $message."&".$name;
								}else{
									$message = $message."§cvs§a".$name;
								}
							}else{
								$message = $name;
							}
							$name1 = $this->system["Teams"][$name];
						}
						Server::getInstance()->broadcastMessage("§a§d[".$this->config->get("XvsY")."] §e".$message."の試合が開始されます");
						foreach($player as $gm0){
							$gm0->setGamemode(0);
						}
						$this->system["GamesRun"] = "on";
						if($this->config->get("アイテム、エフェクト消去") == "on"){
							foreach($player as $ei){
								$ei->removeAllEffects();
								$ei->getInventory()->clearAll();
							}
						}
						foreach($this->config->getAll()["配布アイテム"] as $item){
							$item = explode(":",$item);
							$item = Item::get($item[0],$item[1],$item[2]);
							foreach($player as $i){
								$i->getInventory()->addItem($item);
							}
						}
						foreach($this->config->getAll()["エフェクト"] as $effect){
							$effect = explode(":",$effect);
							if($effect[0] != 0){
								foreach($player as $eff){
									$eff->addEffect(Effect::getEffect($effect[0])->setDuration($effect[1] * 20)->setAmplifier($effect[2])->setVisible(true));
									$eff->addEffect(Effect::getEffect(11)->setDuration(3 * 20)->setAmplifier(10)->setVisible(false));
								}
							}
						}
						foreach($player as $player1){
							$player1->getInventory()->setArmorItem(0,Item::get($this->config->getAll()["装備"]["ヘルメット"],0,1));
							$player1->getInventory()->setArmorItem(1,Item::get($this->config->getAll()["装備"]["チェストプレート"],0,1));
							$player1->getInventory()->setArmorItem(2,Item::get($this->config->getAll()["装備"]["レギンス"],0,1));
							$player1->getInventory()->setArmorItem(3,Item::get($this->config->getAll()["装備"]["ブーツ"],0,1));
							$player1->getInventory()->sendArmorContents($player1);
						}
						foreach($player as $player1){
							$player1->setMaxHealth($this->config->get("最大体力"));
							$player1->save();
						}
						foreach($player as $player1){
							$player1->setHealth($this->config->get("体力"));
							$player1->save();
						}
					}else{
						Server::getInstance()->broadcastMessage("§e§d[".$this->config->get("XvsY")."]§e ".$event->getPlayer()->getName()."が受付をしました");
					}
				}
			}
		}
	}
	public function XvsYDamage(EntityDamageEvent $event){
		if($event instanceof EntityDamageByEntityEvent){
			if($this->system["GamesRun"] == "on"){
				$a = $event->getEntity()->getName();
				$b = $event->getDamager()->getPlayer()->getName();
				if(strpos($this->system["MenberTima"],":")){
					$member = explode(":",$this->system["MenberTima"]);
					$c = $member[count($member) - 1] == $event->getEntity()->getName();
				}else{
					$c = strpos($this->system["MenberTima"],$event->getEntity()->getName());
				}
				if(strpos($this->system["MenberTima"],":".$event->getEntity()->getName().":")||$c||$member[0] == $event->getEntity()->getName()){
					if(isset($this->system["Teams"][$a])){
						if(isset($this->system["Teams"][$b])){
							if($this->system["Teams"][$a] == $this->system["Teams"][$b]){
								$event->getDamager()->getPlayer()->sendPopup("§c同じチームです");
								$event->setCancelled(true);
							}
						}
					}else{
						$event->getDamager()->getPlayer()->sendMessage("§c試合中です");
						$event->setCancelled(true);
					}
				}
			}
		}
	}
	public function XvsYPlayerMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$member = explode(":",$this->system["MenberTima"]);
		if($this->system["Motion"] == "on"){
			if(strpos($this->system["MenberTima"],":".$player->getName().":")||$member[count($member) - 1] == $player->getName()||$member[0] == $event->getPlayer()->getName()){
				if(count($player->usedChunks) >= 56){
					$event->setCancelled(true);
				}
			}
		}
	}
	public function XvsYQuit(PlayerQuitEvent $event) {
		$member = explode(":",$this->system["MenberTima"]);
		if(strpos($this->system["MenberTima"],":")){
			$member = explode(":",$this->system["MenberTima"]);
			$a = $member[count($member) - 1] == $event->getPlayer()->getName();
		}else{
			$a = strpos($this->system["MenberTima"],$event->getPlayer()->getName());
		}
		if(strpos($this->system["MenberTima"],":".$event->getPlayer()->getName().":")||$a||$member[0] == $event->getPlayer()->getName()){
			$member = explode(":",$this->system["MenberTima"]);
			if($this->system["GamesRun"] == "on"){
				Server::getInstance()->broadcastMessage("§d[".$this->config->get("XvsY")."]§e ".$event->getPlayer()->getName()."が退出しました");
				$c = $this->system["Teams"];
				unset($c[$event->getPlayer()->getName()]);
				$this->system["Teams"] = $c;
				$a = $this->system["Teams"];
				$a = array_unique($a);
				$a = array_values($a);
				if(count($a) <= 1){
					$i = 0;
					foreach($this->system["Teams2"] as $name => $wins){
						if($i == 0){
							if($wins == $a[0]){
								$wins1 = $name;
								$i++;
							}
						}else{
							if($wins == $a[0]){
								$wins1 = $wins1."&".$name;
							}
						$i++;
						}
					}
					Server::getInstance()->broadcastMessage("§e§d[".$this->config->get("XvsY")."]§e試合が終了しました\n§d[".$this->config->get("XvsY")."]§e".$wins1."が勝利しました");
					unset($this->system["MenberTima"]);
					$this->system["MenberTima"] = "";
					unset($this->system["GamesRun"]);
					$this->system["GamesRun"] = "";
					unset($this->system["Teams"]);
					$this->system["Teams"] = "";
					unset($this->system["Teams2"]);
					$this->system["Teams2"] = "";
					$this->getServer()->getScheduler()->cancelTasks($this);
					foreach($member as $tp){
						$tp = $this->getServer()->getPlayer($tp);
						if($tp !== null){
							$tp->teleport($this->getServer()->getDefaultLevel()->getSpawn());
							if($this->config->get("アイテム、エフェクト消去") == "on"){
								$tp->removeAllEffects();
								$tp->getInventory()->clearAll();
							}
						}
					}
				}
			}else{
				if($this->system["MenberTima"] == $event->getPlayer()->getName()){
					unset($this->system["MenberTima"]);
					$this->system["MenberTima"] = "";
				}else{
					if(strpos($this->system["MenberTima"],":")){
						if(strpos($this->system["MenberTima"],":".$event->getPlayer()->getName().":")){
							$newmember = str_replace(":".$event->getPlayer()->getName(),"",$this->system["MenberTima"]);
							$this->system["MenberTima"] = $newmember;
						}else{
							if($member[substr_count($this->system["MenberTima"],":") - 1]){
								$newmember = str_replace(":".$event->getPlayer()->getName(),"",$this->system["MenberTima"]);
								$this->system["MenberTima"] = $newmember;
							}else{
								if($member[0] == $event->getPlayer()->getName()){
									$newmember = str_replace(":".$event->getPlayer()->getName(),"",$this->system["MenberTima"]);
									$this->system["MenberTima"] = $newmember;
								}
							}
						}
					}
				}
			}
		}
	}
	public function XvsYDeath(PlayerDeathEvent $event){
		$member = explode(":",$this->system["MenberTima"]);
		if(strpos($this->system["MenberTima"],":")){
			$member = explode(":",$this->system["MenberTima"]);
			$a = $member[count($member) - 1] == $event->getEntity()->getName();
		}else{
			$a = strpos($this->system["MenberTima"],$event->getEntity()->getName());
		}
		if(strpos($this->system["MenberTima"],":".$event->getEntity()->getName().":")||$a||$member[0] == $event->getEntity()->getName()){
			if($this->system["GamesRun"] == "on"){
				$member = explode(":",$this->system["MenberTima"]);
				Server::getInstance()->broadcastMessage("§d[".$this->config->get("XvsY")."]§e ".$event->getEntity()->getName()."が死亡しました");
				$c = $this->system["Teams"];
				unset($c[$event->getEntity()->getName()]);
				$this->system["Teams"] = $c;
				$a = $this->system["Teams"];
				$a = array_unique($a);
				$a = array_values($a);
				if(count($a) <= 1){
					$i = 0;
					foreach($this->system["Teams2"] as $name => $wins){
						if($i == 0){
							if($wins == $a[0]){
								$wins1 = $name;
								$i++;
							}
						}else{
							if($wins == $a[0]){
								$wins1 = $wins1."&".$name;
							}
						$i++;
						}
					}
					Server::getInstance()->broadcastMessage("§e§d[".$this->config->get("XvsY")."]§e試合が終了しました\n§d[".$this->config->get("XvsY")."]§e".$wins1."が勝利しました");
					unset($this->system["MenberTima"]);
					$this->system["MenberTima"] = "";
					unset($this->system["GamesRun"]);
					$this->system["GamesRun"] = "";
					unset($this->system["Teams"]);
					$this->system["Teams"] = "";
					unset($this->system["Teams2"]);
					$this->system["Teams2"] = "";
					$this->getServer()->getScheduler()->cancelTasks($this);
					foreach($member as $tp){
						$tp = $this->getServer()->getPlayer($tp);
						if($tp !== null){
							$tp->teleport($this->getServer()->getDefaultLevel()->getSpawn());
							if($this->config->get("アイテム、エフェクト消去") == "on"){
								$tp->removeAllEffects();
								$tp->getInventory()->clearAll();
							}
						}
					}
				}
			}
		}
	}
}
class XvsYCountDown extends PluginTask{
	public function __construct(PluginBase $owner,$player,$down,$config,$getServer,Config $setting){
		parent::__construct($owner);
		$this->this = $owner;
		$this->player = $player;
		$this->down = $down;
		$this->owner = $owner;
		$this->getServer = $getServer;
		$this->setting = $setting;
	}
	public function onRun($tick){
		foreach($this->player as $player){
			$player->sendPopup("§l".$this->down);
		}
		if($this->down == 0){
			$this->this->system["Motion"] = "off";
			$down = $this->setting->get("制限時間");
			for ( $i = 0; $i < $this->setting->get("制限時間") + 1 ; $i++){
				$task = new XvsY2CountDown($this->this,$this->player,$down,$this->owner,$this->getServer,$this->setting);
				$this->getServer->getScheduler()->scheduleDelayedTask($task,$i*20);
				foreach($this->player as $player){
					$player->sendTip("§c§lstart!!");
					$player->sendTip("§c§lstart!!");
				}
				$down--;
			}
		}
	}
}
class XvsY2CountDown extends PluginTask{
	public function __construct(PluginBase $owner,$player,$down,$config,$getServer,$vs){
		parent::__construct($owner);
		$this->this = $owner;
		$this->player = $player;
		$this->down = $down;
		$this->getServer = $getServer;
		$this->vs = $vs;
	}
	public function onRun($tick){
		foreach($this->player as $player){
			$player->sendPopup("§l".$this->down);
		}
		if($this->down == 0){
			Server::getInstance()->broadcastMessage("§e[".$this->vs->get("XvsY")."]時間切れ");
			unset($this->this->system["MenberTima"]);
			$this->this->system["MenberTima"] = "";
			unset($this->this->system["GamesRun"]);
			$this->this->system["GamesRun"] = "";
			unset($this->this->system["Teams"]);
			$this->this->system["Teams"] = "";
			unset($this->this->system["Teams2"]);
			$this->this->system["Teams2"] = "";
			foreach($this->player as $tp){
				if($tp !== null){
					$tp->teleport($this->getServer->getDefaultLevel()->getSpawn());
				}
			}
		}
	}
}
