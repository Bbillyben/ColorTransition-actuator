
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


$('#bt_deleteAllMotor_Btn').on('click', function(){
	console.log('clic kill');
  $.ajax({
    type: "POST", 
    url: "plugins/ColorTransition_actuator/core/ajax/Colortransition_actuator.ajax.php", 
    data: {
        action: "stop_motor"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) { // si l'appel a bien fonctionné
        if (init(data.state) != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        //console.log(data.result);
       updateMemStatus();
    }
  });
  
	

});

$('#bt_refreshMotor_Btn').on('click', function(){updateMemStatus();});
function updateMemStatus(){
	console.log('clic refresh');
	 $.ajax({
    type: "POST", 
    url: "plugins/ColorTransition_actuator/core/ajax/Colortransition_actuator.ajax.php", 
    data: {
        action: "refresh_mem"
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) { // si l'appel a bien fonctionné
        if (init(data.state) != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        //console.log(data.result);
      $('#txtJsonMotor').val(JSON.stringify(JSON.parse(data.result),null,4));//JSON.stringify(data.result, null, 2));
              
    }
  });
}
updateMemStatus();