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
require_once __DIR__  . '/../../../core/php/core.inc.php';
require_once __DIR__  . '/../core/class/shmSmart.class.php';
require_once __DIR__  . '/../core/class/CT_motor.class.php';

$countLoop = 0;

log::add('ColorTransition_actuator', 'debug', '║ ║ ╟─── MOTOR TICK CALL :'.$argv[1]);

  /// le coeur du moteur  
function CT_motor_startTime($tickTime, $countLoop){
   $countLoop+=1;   
    
    $shx= new shmSmart();
    
    
    if(!$shx->has(CT_motor::SHM_KEY)){
      log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─── MOTOR time no shm ');
      return false;
    }

    log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR TICK '.$countLoop.' | '.microtime(true));
    
    $arr = $shx->get(CT_motor::SHM_KEY);

    $minTime = 150000;// un chiffre haut pour calculer le min
   foreach($arr as $id=>$cta_tr){
    log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─── MOTOR CTA : '.$id);
     $cta_tr['curStep']-=$tickTime;

      if ($cta_tr['curStep'] <= 0 ){  // si on doit mettre à jour
        $eq= $cta_tr['eqL'];
        if(!is_object($eq)){
          log::add('ColorTransition_actuator_mouv', 'error', '║ ║ ╟─── #############" MOTOR Error id :'.$cta_tr['id']);
        }
        $cta_tr['curStep'] = $cta_tr['dur_interval'];// remise à l'initial du compteur
        $cta_tr['move_index']+=$cta_tr['index_step'];
        $eq->refreshEquipementColor($cta_tr['move_index']); 
        log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─── MOTOR new index : '.$cta_tr['move_index']);
        
      }
      //log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─ dur : '.$cta_tr['dur']);
      $cta_tr['dur']-=$tickTime;

      // gestion du temps minimum
      $minTime=min($minTime,$cta_tr['dur'], $cta_tr['curStep']);
      
      if($cta_tr['dur']<=0){
        unset($arr[$id]);
      }else{
      	$arr[$id]=$cta_tr;
      }
   }
   log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─ cache array : '.json_encode($arr));
     
    $shx->put(CT_motor::SHM_KEY,$arr);
    //log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─── MOTOR TICK End  | '.microtime(true));
    if(count($arr)>0 && $countLoop<3600){
      
      if($minTime >=1){
        log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─ sleep time  : '.intval($minTime));
        sleep(intval($minTime));
        CT_motor_startTime(intval($minTime), $countLoop);
      }else{
        log::add('ColorTransition_actuator_mouv', 'debug', '║ ║ ╟─ sleep time  : '.($minTime));
        usleep($minTime*1000000);
        CT_motor_startTime($minTime, $countLoop);
      }
      
    }else{
      $shx->del(CT_motor::SHM_KEY); // delete key
      $shx->remove();
		  unset($shx); // free memory in php..
      log::add('ColorTransition_actuator_mouv', 'info', '║ ║ ╟─---------------------------   MOTOR END : ');
      $countLoop=0;
    }
    
  }
  if($argv[1] >=1){
    sleep(intval($argv[1]));
    CT_motor_startTime(intval($argv[1]), $countLoop);
  }else{
    usleep($argv[1]*1000000);
    CT_motor_startTime($argv[1], $countLoop);
  }


?>