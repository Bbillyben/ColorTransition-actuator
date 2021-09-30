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
  public static function addCTA($eqL, $direction){
    $cta_tr=self::get_transition_def($eqL, $direction);
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
      // temps max d'execution
      $maxTime = config::byKey('CT_motor_maxtime', 'ColorTransition_actuator', 0);
      if($maxTime==0)$maxTime=ColorTransition_actuator::MOTOR_MAX_TIME_DEFAULT;

      $command = '/usr/bin/php '.__DIR__.'/../../ressources/CT_motor_tick.php '.$cta_tr['dur_interval'].' '.$maxTime;
      $output = shell_exec($command.' >/dev/null &');
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR command :'.$command);
    }
  }
  
  // utilitaire : construction de l'array qui défini la transition dans le moteur
   private function get_transition_def($eqL, $direction){
     $serialArray = array();
     $serialArray['id']=$eqL->getId();
     $serialArray['dur_interval']=$eqL->getConfiguration('dur_interval');

     $dur_in=$eqL->getConfiguration('dur_movein');
     $dur_out=$eqL->getConfiguration('dur_moveout');

    $ct_id=$eqL->getConfiguration('ct_equip');
    $CT_equip=eqLogic::byId($ct_id);
    if(!is_object($CT_equip))throw new Exception(__('Equipement ColorTransition non trouvé', __FILE__));
    $bornes=$CT_equip->getBornes();


     $cmd=$eqL->getCmd(null,'curseurIndex');
    if(!is_object($cmd))throw new Exception(__('Commande index courant non trouvé', __FILE__));
    $cur_index=$cmd->execCmd();

     $cmd=$eqL->getCmd(null,'curseurTarget');
    if(!is_object($cmd))throw new Exception(__('Commande index cible non trouvé', __FILE__));
    $cur_target=$cmd->execCmd();
    
    if($cur_target==null){
      if($direction>0){
        $cur_target=$bornes['max'];
      }else{
        $cur_target=$bornes['min'];
        $direction=1;
      }
      
    }

     if($dur_out==0 || $dur_out==null)$dur_out=$dur_in;

     // calcul entre 2 interval
     if($direction == 1){
       $start_index= $cur_index;
       $end_index= $cur_target;
     }else{//move out on inverse curseur et target
       $end_index= $cur_index;
       $start_index= $cur_target;
     }

     $serialArray['dur']=($direction==1)?$dur_in:$dur_out;// durée enb fonction de la direction
     $serialArray['dur_step']=ceil($serialArray['dur']/$serialArray['dur_interval']);

     $serialArray['index_step']=round(($end_index-$start_index)/$serialArray['dur_step'], 3);
     $serialArray['move_index']=$start_index;
     $serialArray['curStep']=$serialArray['dur_interval'];

     $serialArray['eqL']=$eqL;

     

     
     $serialArray['CT_equip']=$CT_equip;
     $serialArray['bornes']=$bornes;
     $serialArray['colorArray']=$CT_equip->getColorsArray();

    


     return $serialArray;

  }
  
}

?>