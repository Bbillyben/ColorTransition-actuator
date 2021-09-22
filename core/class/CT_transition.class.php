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
  public $id = 0;
  private $dur_in=null;
  private $dur_out=null;
  private $dur = 0;
  
  private $dur_interval=null;
  private $cur_index=null;
  private $cur_target=null;
  private $direction = 0;
  
  public $cta_eq = null;
  
  private $dur_step = 0;// la durée d'un step
  private $curStep = 0;// pour le comptage du nombre de step
  
  private $index_step= 0;
  
  private $start_index = 0;
  private $end_index = 0;
  private $move_index = 0;// l'index en cours de mouvement
  
  private $on_duty=false;
   function __construct()
    {
    }
  
  public function refParams(&$cta_eq, $direction){
    $this->cta_eq=$cta_eq;
    $this->id=$cta_eq->getId();
    $this->name=$cta_eq->getHumanName();
    $this->dur_in=$cta_eq->getConfiguration('dur_movein');
    $this->dur_out=$cta_eq->getConfiguration('dur_moveout');
    $this->dur_interval=$cta_eq->getConfiguration('dur_interval');
    
    $cmd=$cta_eq->getCmd(null,'curseurIndex');
   if(!is_object($cmd))throw new Exception(__('Commande index courant non trouvé', __FILE__));
   $this->cur_index=$cmd->execCmd();
    
    $cmd=$cta_eq->getCmd(null,'curseurTarget');
   if(!is_object($cmd))throw new Exception(__('Commande index cible non trouvé', __FILE__));
   $this->cur_target=$cmd->execCmd();
    
    if($this->dur_out==0 || $this->dur_out==null)$this->dur_out=$this->dur_in;
    
    $this->direction=$direction;
    // calcul entre 2 interval
    if($this->direction == 1){
      $this->start_index = $this->cur_index;
      $this->end_index= $this->cur_target;
    }else{//move out on inverse curseur et target
      $this->end_index= $this->cur_index;
      $this->start_index= $this->cur_target;
    }
    
    $this->dur=($this->dir=1)?$this->dur_in:$this->dur_out;// durée enb fonction de la direction
	$this->dur_step=intval($this->dur/$this->dur_interval);
    
    $this->index_step=$this->direction*($this->cur_target-$this->cur_index)/$this->dur_step;
    
    log::add('ColorTransition_actuator', 'debug', '║ ╠════ Transition parameters :'.$cta_eq->getHumanName());
    log::add('ColorTransition_actuator', 'debug', '║ ╠════ direction :'.$direction. ' | '.$this->direction);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── durée in : '.$this->dur_in);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── durée out : '.$this->dur_out);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── durée interval : '.$this->dur_interval);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── index courant : '.$this->cur_index);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── index cible : '.$this->cur_target);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── dur steps : '.$this->dur_step);
    log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── index step : '.$this->index_step);
    
  }
  public function start(){
    $this->move_index=$this->start_index;
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── transition start at index :'.$this->move_index);
   $this->curStep=0;
    
    $this->cta_eq->refreshEquipementColor($this->move_index);
    $this->on_duty=true;
    
  }
  public function stop(){
    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── stop called from eq :');
     $this->on_duty=false;
  }
  public function tick(){
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
  }
  
  

}