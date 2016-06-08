<?php
#  ____  _                                    _    
# |  _ \| |_   _  __ _ _____      _____  _ __| | __
# | |_) | | | | |/ _` / __\ \ /\ / / _ \| '__| |/ /
# |  __/| | |_| | (_| \__ \\ V  V / (_) | |  |   < 
# |_|   |_|\__,_|\__, |___/ \_/\_/ \___/|_|  |_|\_\
#                |___/
# @copyright (c) 2016 All rights reserved, Plugswork
# @author    Plugswork Codx
# @website   https://plugswork.com/

namespace Plugswork\Module;

use Plugswork\Plugswork;

class ChatModule{
    
    private $plugin;
    private $allowUnic, $adGuard, $spamGuard, $capsGuard, $chatHelpers = false;
    private $wordChecker = true;
    private $chatTime = [];
    
    public function __construct(Plugswork $plugin){
        $this->plugin = $plugin;
    }
    
    public function load($rawSettings){
        //Settings handler
        $st = json_decode($rawSettings, true);
        if(!isset($st["allowUnic"])){
            $this->allowUnic = false;
        }
        if(isset($st["enableAd"])){
            $this->adGuard = true;
        }
        if(isset($st["enableSpam"])){
            $this->spamGuard = true;
        }
        if(isset($st["enableCaps"])){
            $this->capsGuard = true;
        }
        if(isset($st["enableHelpers"])){
            $this->chatHelpers = true;
        }
        /*if(isset($st["wordChecker"])){
            $this->wordChecker = true;
        }*/
        $st["bWords"] = explode(",", $st["bWords"]);
        $this->settings = $st;
        
    }
    
    public function check($pn, $msg){
        $res = [];
        if(!$this->allowUnic){
            if(strlen($msg) != strlen(utf8_decode($msg))){
                $res["action"] = "chat";
                $res["message"] = "chat.unicWarning";
                return $res;
            }
        }
        if($this->spamGuard){
            $tick = $this->plugin->getServer()->getTick();
            if($this->chatTime[$pn] == null){
                $this->chatTime[$pn] = $tick;
            }
            if($this->chatTime[$pn] >= $tick){
                $res["action"] = $this->settings["spamAction"];
                $res["message"] = "chat.spamWarning";
                return $res;
            }
            $this->chatTime[$pn] = $tick + ($this->settings["spamRestDur"] * 20);
        }
        if($this->adGuard){
            if(preg_match("/[A-Z0-9]+\.[A-Z0-9]+/i", $msg)){
                $res["action"] = $this->settings["adAction"];
                $res["message"] = "chat.adWarning";
                return $res;
            }
        }
        if($this->capsGuard){
            $count = strlen(preg_replace('![^A-Z]+!', '', $msg));
            if($count >= $this->options["maxCaps"]){
                $res["action"] = $this->settings["capsAction"];
                $res["message"] = "chat.capsWarning";
                return $res;
            }
        }
        if($this->wordChecker){
            foreach($this->settings["bWords"] as $word){
                if(strpos($msg, $word) !== false){
                    $res["action"] = $this->settings["bwAction"];
                    $res["message"] = "chat.bwWarning";
                    return $res;
                }
            }
        }
        if($this->chatHelpers){
            foreach($this->settings["helpers"] as $key => $tip){
                if(strpos($msg, $key) !== false){
                    $res["action"] = "";
                    $res["message"] = $tip;
                    return $res;
                }
            }
        }
    }
    
}