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
require_once __DIR__  . '/CT_motor.class.php';


class ColorTransition_actuator extends eqLogic {

   const logID_common = array(
      'currentColor',
      'curseurIndex',
      'setCurseurIndex',
      'curseurTarget',
      'setCurseurTarget',
      'move_in',
      'move_out',
      'currentColor',
      'status'
   );
const MOTOR_MAX_TIME_DEFAULT = 7200;// 2 heures
const MOTOR_MIN_TIME_UPDATE_DEFAULT = 0.5;// 1/2 secondes
  
//protected $CT_equip = null; // l'quipement color transform, pour éviter le calcul à chaque itération
//protected $colorArray = null; // l'array de couleur à soumettre à l'équipement ColorTransform, pour éviter le calcul à chaque itération
//protected $bornes = null;
  
  
/* --------------------------  Fonctiond e cron pour check le process bien kill -------------------------------------- */
 public static function cronDaily() {
	log::add('ColorTransition_actuator', 'debug', '╔═══════════════════════ START CRON DAILY');
   // get max time of execution
   $maxTime = config::byKey('CT_motor_maxtime', __CLASS__, 0);
   if($maxTime==0)$maxTime=self::MOTOR_MAX_TIME_DEFAULT;
   log::add('ColorTransition_actuator', 'debug', '╟─── MOTOR max execution time :'.$maxTime);
   // vérification des pid sur le php
	$pids = array();
    $pid = exec("pgrep -f CT_motor_tick.php", $pids);
    log::add('ColorTransition_actuator', 'debug', '╟─── MOTOR PID :'.json_encode($pids).' | '.count($pids));
   	if(count($pids)>1){// si il y a un moteur qui tourne
      for($i=0; $i<count($pids)-1; $i++){
        $execTime=intval(exec('ps -p '.$pids[$i].' -o etimes'));
        log::add('ColorTransition_actuator', 'debug', '╟─── MOTOR PID infos :'.$i.' | '.$pids[$i].' - exec time : '.$execTime);
        if($execTime > $maxTime){
          log::add('ColorTransition_actuator', 'debug', '╟─── MOTOR KILL PID :'.$pids[$i]);
        	//kill the process 
          	 $output=exec('kill -9 '.$pids[$i]);
          	// libération de la mémoire ocazou
            $shx= new shmSmart();
          	if($shx->has(CT_motor::SHM_KEY)){
              // remise à 0 des équipements 
              foreach($shx->get(CT_motor::SHM_KEY) as $id=>$cta_tr){
                $eq= $cta_tr['eqL'];
                if(is_object($eq))$eq->endMove();
              }
              $shx->del(CT_motor::SHM_KEY);
            }
            $shx->remove();
            unset($shx);              
        }
      }
    }
	log::add('ColorTransition_actuator', 'debug', '╚═══════════════════════ END CRON ');
  }

//
public function start_move($direction){
   //cache::delete('COLOR_TRANSITION::serial_array');
   log::add('ColorTransition_actuator','debug', "║ ╔═══════════════════════ Start move / direction : ".$direction);
   $cmdA = $this->getCommandArray();
   // récup du tableau de couleurs de l'équipement CT
   $ct_id=$this->getConfiguration('ct_equip');
   $ct_eq=eqLogic::byId($ct_id);
   if(!is_object($ct_eq))throw new Exception(__('Equipement ColorTransition non trouvé', __FILE__));
  
   
   $this->colorArray=$ct_eq->getColorsArray();
  
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── equipement CT : '.$ct_eq->getHumanName());
      log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── Couleurs : '.json_encode($this->colorArray));
      log::add('ColorTransition_actuator', 'debug', '║ ╚═════════════════════════════');
  	
     CT_motor::addCTA($this, $direction);
     $this->getCmd(null, 'status')->event(1);
}
  
 public function stopMove(){
   log::add('ColorTransition_actuator', 'debug', '║ STOP MOVE CALLED :');
   CT_motor::removeCTA($this->getId());
   $this->endMove();
 }
 
 public function endMove(){
  log::add('ColorTransition_actuator', 'debug', '║ END MOVE CALLED');
  $this->getCmd(null, 'status')->event(0);
  // mise à null de la cible
  $ctCMD = $this->getCmd(null, 'curseurTarget');
    if (is_object($ctCMD)) {
      $ctCMD->event(null);
    }
 }

  
// set des equipement en fonc tion du curseur courant
  public function refreshEquipementColor($cursorIndex, $CT_equip=null,$bornes=null, $colorArray=null ){
    log::add('ColorTransition_actuator', 'debug', '║ ╠════ Update colors, index '.$this->getId().' :'.$cursorIndex);
    
    // vérif valorisation CT equipement
    if($CT_equip == null){
      log::add('ColorTransition_actuator', 'debug', '║ ╠════ ####################### CT Equip not found ');
      $ct_id=$this->getConfiguration('ct_equip');
      $CT_equip=eqLogic::byId($ct_id);
      if(!is_object($CT_equip))throw new Exception(__('Equipement ColorTransition non trouvé', __FILE__));
    }
    // vérif des bornes
    if($bornes == null)$bornes=$CT_equip->getBornes();
    // calcul du %
    $cursorIndex=min(max($cursorIndex,$bornes['min']),$bornes['max']);
    $cursPos=($cursorIndex-$bornes['min'])/($bornes['max']-$bornes['min']);
   // vérif color array défini
    if($colorArray==null)$colorArray=$CT_equip->getColorsArray();
   
    
    // gestion des actionneurs
    $actuators=$this->getCommandArray();
    $calculatedColors=array();
    
    foreach($actuators as $actuator){
      //log::add('ColorTransition_actuator', 'debug', '║ ╟─── actuator : '.json_encode($actuator));
      $colorId=$actuator['color-format'].$actuator['use_alpha'].$actuator['use_white'];
      if(!in_array($colorId, $calculatedColors)){
        $calculatedColors[$colorId] = $CT_equip->calculateColorFromIndex($cursPos, $colorArray,$actuator['use_alpha'],$actuator['use_white'],$actuator['color-format']);
      }
      $color = $calculatedColors[$colorId];
      switch($actuator['act_type']){
        case 'action':
          	$this->refreshCmdColor($actuator, $color);
          break;
        case 'scenario':
          $this->refreshScenarColor($actuator, $color);
          break;
      }
    }
    //valorisation de l'info current color de l'équipement
    $ctCMD = $this->getCmd(null, 'currentColor');
    if (!is_object($ctCMD)) {
       log::add('ColorTransition_actuator', 'error', '### Couleur Courante non trouvée ');
      return;
    }
    $ctCMD->event($color);
   // mise à jour du curseur index
   $ctCMD = $this->getCmd(null, 'curseurIndex');
    if (!is_object($ctCMD)) {
       log::add('ColorTransition_actuator', 'error', '### Curseur Courante non trouvée ');
      return;
    }
     $ctCMD->event($cursorIndex);
  }

  public function refreshCmdColor($actuator, $color){
    // récup de la commande
    $cmd=cmd::byId(str_replace('#','',$actuator['sendCmd']));
    if(!is_object($cmd)){
      log::add('ColorTransition_actuator', 'error', '### actuator '.$actuator['sendCmd'].' NOT FOUND: '.json_encode($actuator));
      return;
    }
    //log::add('ColorTransition_actuator', 'debug', '║ ╟─── cmd type : '.$cmd->getType());
    switch($cmd->getType()){
      case 'info':
        //log::add('ColorTransition_actuator', 'debug', '║ ╟─── color event on info : '.$color);
        $cmd->event($color);
        break;
      case 'action':
        //log::add('ColorTransition_actuator', 'debug', '║ ╟─── color action on '.$actuator['dest'].' : '.$color);
        $optionsSendCmd=array('message'=>null, 'title'=>null, 'slider'=>null, 'color'=>null );
        $optionsSendCmd[$actuator['dest']]=$color;
        $cmd->execCmd($optionsSendCmd);
        break;
      default:
        	log::add('ColorTransition_actuator', 'error', '### command type unkwnon '.$cmd->getType());
        break;
    }
  }
  public function refreshScenarColor($actuator, $color){
    $scenario=scenario::byId(str_replace(array('#','scenario'),array('',''),$actuator['sendScenar']));
    if(is_null($scenario)){
			log::add('ColorTransition_actuator', 'error', 'Scenario d\'envoi non trouvé '.$actuator['sendScenar']);
			return false;
		}
    //log::add('ColorTransition_actuator', 'debug', '║ ╟─── Scenario : '.$scenario->getHumanName());
    $files=array();
    $files["#color#"]=$color;
    $scenario->setTags($files);
	$scenario->launch();
    
  }
// utilitaire 
// construction de l'array des commandes
public function getCommandArray(){
   $cmdA = Array();
   
   $allCmds = $this->getCmd('info');
   
   // On récupère les configurations des couleurs parmi les cmd info - ss logicalId
      foreach($allCmds as $cmdCol){
        if(in_array($cmdCol->getLogicalId(),ColorTransition_actuator::logID_common)==false){
         $cmdA[]=$cmdCol->getConfiguration();
        }
      }
   
      return $cmdA;
 }

    /*     * *********************Méthodes d'instance************************* */
    
 // Fonction exécutée automatiquement avant la création de l'équipement 
    public function preInsert() {
        
    }

 // Fonction exécutée automatiquement après la création de l'équipement 
    public function postInsert() {
        
    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
    public function preUpdate() {   
      // vérification avvant sauvegarde
        // le min de mise à jour des transition
        $value=$this->getConfiguration('dur_interval');
        $minTime = config::byKey('CT_motor_minupdate', __CLASS__, 0);
        if($minTime==0)$minTime=self::MOTOR_MIN_TIME_UPDATE_DEFAULT;
        if($minTime>$value)throw new Exception(__('La valeur de temps de mise à jour doit être supérieur à :', __FILE__).$minTime);
      
      // la durée de move in
      
      $value=$this->getConfiguration('dur_movein');
      if(empty($value) || $value = "" || $value < 0)throw new Exception(__('La valeur de Durée Move In ne peut pas être vide', __FILE__));
      
      // le ct_equip
      $value=$this->getConfiguration('ct_equip');
      if(empty($value) || $value = "" || $value < 0)throw new Exception(__('Vous devez sélectionner un équipement ColorTrnasition', __FILE__));
      
      
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement 
    public function postUpdate() {
        
    }
 

 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
    public function preSave() {
       //rendre les actioneurs non visibles
      $allCmds = $this->getCmd('info');
      foreach($allCmds as $cmdCol){
        $cmdLID=$cmdCol->getLogicalId();
        if(in_array($cmdLID,ColorTransition_actuator::logID_common)==false && $cmdCol->getIsVisible() == 1){
          //log::add('ColorTransition', 'debug', '╠════ set non visible cmd : '.$cmdCol->getName());
          $cmdCol->setIsVisible(0);
          $cmdCol->save(true);
          
        }
      }
      
      
        
    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
    public function postSave() {
      // récup de l'équipement ColorTransition pour les bornes
      // récup du tableau de couleurs de l'équipement CT
      $ct_id=$this->getConfiguration('ct_equip');
      $ct_eq=eqLogic::byId($ct_id);
      if(!is_object($ct_eq)){
        if(!is_null($ct_id) && $ct_id!="" && $ct_id >0 ){
        	throw new Exception(__('Equipement ColorTransition non trouvé', __FILE__));
        }
      }else{
        $this->bornes=$ct_eq->getBornes();
      	log::add('ColorTransition_actuator', 'debug', '╠════ bornes CT eq : '.json_encode($this->bornes)); 
      
      }
      
      
      // commande info de la valeur de la couleur
      $ctCMD = $this->getCmd(null, 'currentColor');
    if (!is_object($ctCMD)) {
       $ctCMD = new ColorTransition_actuatorCmd();
       $ctCMD->setLogicalId('currentColor');
       $ctCMD->setIsVisible(0);
       $ctCMD->setName(__('Couleur Courante', __FILE__));
       $ctCMD->setTemplate('dashboard', 'ColorTransition::colorText');
       $ctCMD->setTemplate('mobile', 'ColorTransition::colorText');
    }
    
    $ctCMD->setType('info');
    $ctCMD->setSubType('string');
    $ctCMD->setEqLogic_id($this->getId());
    $ctCMD->save();
     // commande info du status
     $ctCMD = $this->getCmd(null, 'status');
     if (!is_object($ctCMD)) {
        $ctCMD = new ColorTransition_actuatorCmd();
        $ctCMD->setLogicalId('status');
        $ctCMD->setIsVisible(1);
        $ctCMD->setName(__('Status', __FILE__));
     }
     
     $ctCMD->setType('info');
     $ctCMD->setSubType('binary');
     $ctCMD->setEqLogic_id($this->getId());
      
    $ctCMD->save();
   // commande info de la valeur de curseur
    $ctCMD = $this->getCmd(null, 'curseurIndex');
    if (!is_object($ctCMD)) {
       $ctCMD = new ColorTransition_actuatorCmd();
       $ctCMD->setLogicalId('curseurIndex');
       $ctCMD->setIsVisible(0);
       $ctCMD->setName(__('Curseur', __FILE__));
    }
    
    $ctCMD->setType('info');
    $ctCMD->setSubType('numeric');
      $ctCMD->setConfiguration('minValue',$this->bornes['min']);
    $ctCMD->setConfiguration('maxValue',$this->bornes['max']);
    $ctCMD->setEqLogic_id($this->getId());
      
    $ctCMD->save();

      // cmd de set du curseur
      //log::add('ColorTransition', 'debug', '╠════ cmd I value to link : '.$ctCMD->getId()); 
      $ctCMDAct = $this->getCmd(null, 'setCurseurIndex');
      if (!is_object($ctCMDAct)) {
         $ctCMDAct = new ColorTransition_actuatorCmd();
         $ctCMDAct->setLogicalId('setCurseurIndex');
         $ctCMDAct->setIsVisible(1);
         $ctCMDAct->setName(__('Set Curseur', __FILE__));
         $ctCMDAct->setTemplate('dashboard', 'ColorTransition::rangedSlider');
         $ctCMDAct->setTemplate('mobile', 'ColorTransition::rangedSlider');
      }
      
      $ctCMDAct->setValue($ctCMD->getId());
      $ctCMDAct->setType('action');
      $ctCMDAct->setSubType('slider');
      $ctCMDAct->setConfiguration('minValue',$this->bornes['min']);
		$ctCMDAct->setConfiguration('maxValue',$this->bornes['max']);
      
      $ctCMDAct->setEqLogic_id($this->getId());
      
      //save
      $ctCMDAct->save();

      // commande info de la valeur de curseur cible
    $ctCMD = $this->getCmd(null, 'curseurTarget');
    if (!is_object($ctCMD)) {
       $ctCMD = new ColorTransition_actuatorCmd();
       $ctCMD->setLogicalId('curseurTarget');
       $ctCMD->setIsVisible(0);
       $ctCMD->setName(__('Cible Curseur', __FILE__));
    }
    $ctCMD->setType('info');
    $ctCMD->setSubType('numeric');
      $ctCMD->setConfiguration('minValue',$this->bornes['min']);
    $ctCMD->setConfiguration('maxValue',$this->bornes['max']);
    $ctCMD->setEqLogic_id($this->getId());
      
    $ctCMD->save();

      // cmd de set du curseur
      //log::add('ColorTransition', 'debug', '╠════ cmd I value to link : '.$ctCMD->getId()); 
      $ctCMDAct = $this->getCmd(null, 'setCurseurTarget');
      if (!is_object($ctCMDAct)) {
         $ctCMDAct = new ColorTransition_actuatorCmd();
         $ctCMDAct->setLogicalId('setCurseurTarget');
         $ctCMDAct->setIsVisible(1);
         $ctCMDAct->setName(__('Set Cible', __FILE__));
         $ctCMDAct->setTemplate('dashboard', 'ColorTransition::rangedSlider');
         $ctCMDAct->setTemplate('mobile', 'ColorTransition::rangedSlider');
      }
      
      $ctCMDAct->setValue($ctCMD->getId());
      $ctCMDAct->setType('action');
      $ctCMDAct->setSubType('slider');
      $ctCMDAct->setConfiguration('minValue',$this->bornes['min']);
    $ctCMDAct->setConfiguration('maxValue',$this->bornes['max']);
      $ctCMDAct->setEqLogic_id($this->getId());
      
      //save
      $ctCMDAct->save();
  
      // action de movein et move out
      $ctCMDAct = $this->getCmd(null, 'move_in');
      if (!is_object($ctCMDAct)) {
         $ctCMDAct = new ColorTransition_actuatorCmd();
         $ctCMDAct->setLogicalId('move_in');
         $ctCMDAct->setIsVisible(1);
         $ctCMDAct->setName(__('Move in', __FILE__));
      }
      $ctCMDAct->setType('action');
      $ctCMDAct->setSubType('other');
      $ctCMDAct->setEqLogic_id($this->getId());
      $ctCMDAct->save();
      
      $ctCMDAct = $this->getCmd(null, 'move_out');
      if (!is_object($ctCMDAct)) {
         $ctCMDAct = new ColorTransition_actuatorCmd();
         $ctCMDAct->setLogicalId('move_out');
         $ctCMDAct->setIsVisible(1);
         $ctCMDAct->setName(__('Move Out  ', __FILE__));
      }
      $ctCMDAct->setType('action');
      $ctCMDAct->setSubType('other');
      $ctCMDAct->setEqLogic_id($this->getId());
      $ctCMDAct->save();
	
      
      $ctCMDAct = $this->getCmd(null, 'stop');
      if (!is_object($ctCMDAct)) {
         $ctCMDAct = new ColorTransition_actuatorCmd();
         $ctCMDAct->setLogicalId('stop');
         $ctCMDAct->setIsVisible(1);
         $ctCMDAct->setName(__('Stop  ', __FILE__));
      }
      $ctCMDAct->setType('action');
      $ctCMDAct->setSubType('other');
      $ctCMDAct->setEqLogic_id($this->getId());
      $ctCMDAct->save();

    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement 
    public function preRemove() {
        
    }

 // Fonction exécutée automatiquement après la suppression de l'équipement 
    public function postRemove() {
        
    }

    /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class ColorTransition_actuatorCmd extends cmd {
    

  // Exécution d'une commande  
     public function execute($_options = array()) {

      log::add('ColorTransition_actuator','debug', "╔═══════════════════════ execute CMD : ".$this->getId()." | ".$this->getHumanName().", logical id : ".$this->getLogicalId() ."  options : ".print_r($_options));
      log::add('ColorTransition_actuator','debug', '╠════ Eq logic '.$this->getEqLogic()->getHumanName());
      
      switch($this->getLogicalId()){
         case 'setCurseurIndex':
          	$this->getEqLogic()->refreshEquipementColor($_options['slider']);
         case 'setCurseurTarget':
          	$cmdInfo = cmd::byId($this->getValue());
          	if(is_object($cmdInfo))$cmdInfo->event($_options['slider']);
          	
         	break;
         case 'move_in';
         $this->getEqLogic()->start_move(1);
         break;
         case 'move_out':
            $this->getEqLogic()->start_move(-1);
            break;
         case 'stop':
            $this->getEqLogic()->stopMove();
            break;
         Default:
         log::add('ColorTransition_actuator','debug', '╠════ Default call');

      } 
      log::add('ColorTransition_actuator','debug', "╚═════════════════════════════════════════ END execute CMD ".$this->getHumanName());


        
     }

    /* public function event($_value, $_datetime = null, $_loop = 1) {
      parent::event($_value, $_datetime, $_loop);

      switch ($this->getLogicalId()) {
         case 'curseurIndex':
               $this->getEqLogic()->refreshEquipementColor($_value);
             break;
         case '':

            break;
      }

      
    }*/

    /*     * **********************Getteur Setteur*************************** */
}
?>