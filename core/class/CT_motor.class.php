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
  // ajout d'un élément dans le cache
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
    $shx->put(self::SHM_KEY,$arr);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR global arra '.$keyName.' | '.count($arr).' : '.json_encode( $shx->get(self::SHM_KEY)));

    // mise à l'index initial
    $eq= $cta_tr["eqL"];
    if(!is_object($eq)){
      log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
    }
    $eq->refreshEquipementColor($cta_tr['move_index']); 
	 
    if(count($arr)==1){
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── #############" MOTOR ask for starting');
      self::$countLoop = 0;
      self::startTime();
      
    }
  }


  /// le coeur du moteur
  private static function startTime(){
     
   self::$countLoop+=1;   
    
    $shx= new shmSmart();
    
    
    if(!$shx->has(self::SHM_KEY)){
      log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─── MOTOR time no shm ');
      return false;
    }

    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR TICK '.self::$countLoop);
    
    $arr = $shx->get(self::SHM_KEY);
   foreach($arr as $id=>$cta_tr){
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR CTA : '.$id);
     $cta_tr['curStep']+=1;

      if ($cta_tr['curStep'] >= $cta_tr['dur_interval']){  // si on doit mettre à jour
        $eq= $cta_tr['eqL']; //eqLogic::byId($cta_tr['id']);
        if(!is_object($eq)){
          log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
        }
        $cta_tr['curStep']=0;
        $cta_tr['move_index']+=$cta_tr['index_step'];
        $eq->refreshEquipementColor($cta_tr['move_index']); 
        
      }
      //log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─ dur : '.$cta_tr['dur']);
      $cta_tr['dur']-=1;
      
      if($cta_tr['dur']<=0){
        unset($arr[$id]);
      }else{
      	$arr[$id]=$cta_tr;
      }
   }
   log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─ cache array : '.json_encode($arr));
     
    $shx->put(self::SHM_KEY,$arr);
    
    if(count($arr)>0 && self::$countLoop<30){
      sleep(1);
      self::startTime();
    }else{
      $shx->del(self::SHM_KEY); // delete key
      $shx->remove();
		unset($shx); // free memory in php..
      log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─---------------------------   MOTOR END : ');
      self::$countLoop=0;
    }
    
  }
  
}