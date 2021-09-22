<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class CT_motor extends eqLogic {
  
  public $countLoop=0;
  
  private static $_instance = null;
  private static $eq_array=Array();
  private $on_air=false;
  
  
      private function __construct() {  
   }

   public static function getInstance() {
 
     if(is_null(self::$_instance)) {
       log::add('ColorTransition_actuator', 'info', '║ ║ ╟─── CT MOTOR CONSTRUCTION : ');
       self::$_instance = new CT_motor();  
     }
     return self::$_instance;
   }
  public function addCTA(&$cta_tr){
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR ADD : '.$cta_tr->name);
   if(in_array($cta_tr->name, self::$eq_array))return false;
    $ctname=$cta_tr->name;
    self::$eq_array[$ctname]=$cta_tr;
    $cta_tr->start();
    
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR On AIr : '.$this->on_air);
   	if(!$this->on_air)$this->startTime();
    return true;
  	
  }

  private function startTime(){
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR TICK : '.$this->on_air);
    $this->countLoop+=1;
    $this->on_air = true;
    
   foreach(self::$eq_array as $cta_tr){
     // vérif que pas demander l'arrêt
     $eq= eqLogic::byId($cta_tr->id);
     $onEq=$eq->getConfiguration('on_air');
     log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─--------- info config : '.$onEq);
      $on=$cta_tr->tick();
      if(!$on || !$onEq){
        log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─---------remove from motor : '.$cta_tr->name);
        unset(self::$eq_array[$cta_tr->name]);
      }
    }
    if(count(self::$eq_array)>0 && $this->countLoop<100){
      sleep(1);
      $this->startTime();
    }else{
      $this->on_air = false;
      log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─---------------------------   MOTOR END : '.$this->on_air);
     $this->countLoop=0;
    }
  }
  
}