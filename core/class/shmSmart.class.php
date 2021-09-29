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

class shmSmart{
  static $MEM_SIZE=33554432;
  static $MEM_ID=0x701da13b;  
  public $shm;            //holds shared memory resource
  
  public function __construct(){
    $this->attach();    //create resources (shared memory)
  }
  public function attach(){
    $this->shm=shm_attach(self::$MEM_ID,self::$MEM_SIZE);    //allocate shared memory
  }
  public function dettach(){
    return shm_detach($this->shm);    //free shared memory
  }
  public function remove(){
    return shm_remove($this->shm);    //dallocate shared memory
  }
  public function put($key,$var) {
    return shm_put_var($this->shm,$this->shm_key($key),$var);    //store var
  }
  public function get($key){
    if($this->has($key)){
      return shm_get_var($this->shm,$this->shm_key($key));  //get var
    }else{
      return false;   
    }       
  }
  public function del($key){
    if($this->has($key)){
      return shm_remove_var($this->shm,$this->shm_key($key)); // delete var
    }else{
      return false;   
    }       
  }
  public function has($key){
    if(shm_has_var($this->shm,$this->shm_key($key))){ // check is isset
      return true;       
    }else{
      return false;       
    }
  }
  public function shm_key($val){ // enable all world langs and chars !
    return preg_replace("/[^0-9]/","",(preg_replace("/[^0-9]/","",md5($val))/35676248)/619876); // text to number system.
  }
  public function __wakeup() {
    $this->attach();
  }
  public function __destruct() {
    $this->dettach();
    //unset($this);
  }   
 
}

?>