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
  private const CT_CACHE_NAME="COLOR_TRANSITION::";
  private const CT_CACHE_ARRAY="serial_array";

  public static $countLoop=0;
  
  private function __construct() {  
  }
  //remove d'un élément par id
  public static function removeCTA($id){
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR REMOVE '.$id);
    $jcacheExist=cache::exist(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY);
   
    
    if(!$jcacheExist)return false;
    $cacheJson=cache::byKey(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY)->getValue();
    $cacheArr=json_decode($cacheJson,true);

    //log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── old '.json_encode($cacheArr));
    //log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── is id removable '.(in_array(strval($id), $cacheArr)?1:0));
    //log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── test : '.(is_null($cacheArr[$id])?0:1));
    if(!is_null($cacheArr[$id])){
      unset($cacheArr[$id]);
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── new cache '.json_encode($cacheArr));
      cache::set(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY, json_encode($cacheArr));      
      return true;
    }else{
      return false;
    }
  }
  // ajout d'un élément dans le cache
  public static function addCTA($cta_tr){
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR ADD '.self::CT_CACHE_NAME.self::CT_CACHE_ARRAY);
    $jcacheExist=cache::exist(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY);

    
    if($jcacheExist){
      $cacheJson=cache::byKey(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY)->getValue();
      $cacheArr=json_decode($cacheJson,true);
    }else{
      $cacheArr=Array();
    }
    $arrAdd=$cta_tr->getArray();
    $keyName=strval($arrAdd['id']);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR cta arra '.$keyName.' : '.json_encode($arrAdd));
    
    $cacheArr[$keyName]=$arrAdd;
    cache::set(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY, json_encode($cacheArr));

    // mise à l'index initial
    $eq= eqLogic::byId($arrAdd['id']);
    if(!is_object($eq)){
      log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
    }
    $eq->refreshEquipementColor($arrAdd['move_index']); 

    if(!$jcacheExist || count($cacheArr)==1)self::startTime();
  }


  /// le coeur du moteur
  private static function startTime(){
    self::$countLoop+=1;

    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR TICK '.self::$countLoop);
    $jcacheExist=cache::exist(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY);
    if(!$jcacheExist)return;
    $cacheArr=json_decode(cache::byKey(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY)->getValue(),true);

    $finalArray=array();
    
   foreach($cacheArr as $id=>$cta_tr){
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR CTA : '.$id);
     $cta_tr['curStep']+=1;

      if ($cta_tr['curStep'] >= $cta_tr['dur_interval']){  // si on doit mettre à jour
        $eq= eqLogic::byId($cta_tr['id']);
        if(!is_object($eq)){
          log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
        }
        $cta_tr['curStep']=0;
        $cta_tr['move_index']+=$cta_tr['index_step'];
        $eq->refreshEquipementColor($cta_tr['move_index']); 
        
      }
      //log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─ dur : '.$cta_tr['dur']);
      $cta_tr['dur']-=1;
      
      if($cta_tr['dur']>0){
        $finalArray[$id]=$cta_tr;
      }
   }
   log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─ cache array : '.json_encode($finalArray));
     
    if(count($finalArray)>0 && self::$countLoop<100){
      cache::set(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY, json_encode($finalArray));
      sleep(1);
      self::startTime();
    }else{
      cache::delete(self::CT_CACHE_NAME.self::CT_CACHE_ARRAY);
      log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─---------------------------   MOTOR END : ');
      self::$countLoop=0;
    }
    
  }
  
}