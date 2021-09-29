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
require_once __DIR__  . '/shmSmart.class.php';

class CT_motor {
  public const SHM_KEY="colortransition_shm";
  public static $countLoop=0;
  
  private function __construct() {  
  }
  //remove d'un élément par id
  public static function removeCTA($id){
    
    $shx= new shmSmart();
    if($shx->has(self::SHM_KEY)){
      $arr = $shx->get(self::SHM_KEY);
    }else{
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR REMOVE #No memory allocated');
      $shx->remove();
      unset($shx);
      return true;
      
    }
    
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR REMOVE '.$id);   
    

    if(!is_null($arr[$id])){
      unset($arr [$id]);
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── new mem '.json_encode($arr));
      $shx->put(self::SHM_KEY,$arr);
      return true;
    }else{
      return false;
    }
  }
  // ajout d'un élément dans la mémoire réservée
  public static function addCTA($cta_tr){
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR ADD ');    
    $shx= new shmSmart();    
    if($shx->has(self::SHM_KEY)){
      $arr = $shx->get(self::SHM_KEY);
    }else{
    	$arr=Array();
    }
    //$arrAdd=$cta_tr->getArray();
    $keyName=strval($cta_tr['id']);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR cta arra '.$keyName.' : '.json_encode($cta_tr));
    
   	$arr[$keyName]=$cta_tr;
    $success=$shx->put(self::SHM_KEY,$arr);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR global arra '.$keyName.' | '.count($arr).'_'.($success?1:0).' : '.json_encode( $shx->get(self::SHM_KEY)));

    // mise à l'index initial
    $eq= $cta_tr["eqL"];
    if(!is_object($eq)){
      log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
    }
    $eq->refreshEquipementColor($cta_tr['move_index']); 
	 
    if(count($arr)==1){
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── ############# MOTOR ask for starting');
      $output = shell_exec('/usr/bin/php '.__DIR__.'/../../ressources/CT_motor_tick.php '.$cta_tr['dur_interval'].' >/dev/null &');
    }
  }
  
}

?>