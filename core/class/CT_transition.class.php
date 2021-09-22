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

class CT_transition extends eqLogic {
  
  public $name=null;
  public $serialArray=Array();

   function __construct()
    {
    }
  
  public function refParams(&$cta_eq, $direction){
    $this->serialArray['id']=$cta_eq->getId();
    $this->serialArray['name']=$cta_eq->getHumanName();
    $this->serialArray['dur_interval']=$cta_eq->getConfiguration('dur_interval');
    
    $dur_in=$cta_eq->getConfiguration('dur_movein');
    $dur_out=$cta_eq->getConfiguration('dur_moveout');

    
    
    $cmd=$cta_eq->getCmd(null,'curseurIndex');
   if(!is_object($cmd))throw new Exception(__('Commande index courant non trouvé', __FILE__));
   $cur_index=$cmd->execCmd();
    
    $cmd=$cta_eq->getCmd(null,'curseurTarget');
   if(!is_object($cmd))throw new Exception(__('Commande index cible non trouvé', __FILE__));
   $cur_target=$cmd->execCmd();
    
    if($dur_out==0 || $dur_out==null)$dur_out=$dur_in;
    
    // calcul entre 2 interval
    if($direction == 1){
      $start_index= $cur_index;
      $end_index= $cur_target;
    }else{//move out on inverse curseur et target
      $end_index= $cur_index;
      $start_index= $cur_target;
    }
    
    $this->serialArray['dur']=($direction=1)?$dur_in:$dur_out;// durée enb fonction de la direction
	  $this->serialArray['dur_step']=intval($this->serialArray['dur']/$this->serialArray['dur_interval']);
   
    $this->serialArray['index_step']=($end_index-$start_index)/$this->serialArray['dur_step'];
    $this->serialArray['move_index']=$start_index;
    $this->serialArray['curStep']=0;
 
    //log::add('ColorTransition_actuator', 'debug', '║ ╠════ Transition parameters :'.$cta_eq->getHumanName());
    //log::add('ColorTransition_actuator', 'debug', '║ ╠════ array serialized :'.json_encode($this->serialArray));
    
  }
  public function getArray(){
    return $this->serialArray;
  }
  
  /*public function tick(){
    $this->curStep+=1;
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── [ : '.$this->name.' tick '. $this->dur.' | step curr :'.$this->curStep.' / '.$this->dur_interval.'   #on duty:'.$this->on_duty);
    
    if($this->on_duty == false)return false;// pour le remove du moteur
    
    if ($this->curStep >= $this->dur_interval){
      log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── update color');
      $this->curStep=0;
      $this->move_index+=$this->index_step;
      $this->cta_eq->refreshEquipementColor($this->move_index);      
    }
    $this->dur-=1;
    if($this->dur<=0){
      $this->on_duty=false;
      return false;
    }
    return true;
  }*/
  
  

}