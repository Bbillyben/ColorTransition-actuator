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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Temps Maximum d'exécution du moteur}}
        <sup><i class="fas fa-question-circle tooltips" title="{{par défaut 7 200 secondes}}"></i></sup>
      </label>
      <div class="col-lg-1">
        <input type="number" class="configKey form-control" data-l1key="CT_motor_maxtime" placeholder="7200"/>
      </div>
      <div class="col-xs-2">
        <span> {{secondes}}</span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Temps minimum de mise à jour de la transition}}
        <sup><i class="fas fa-question-circle tooltips" title="{{un temps trop court ralentira tout le process}}"></i></sup>
      </label>
      <div class="col-lg-1">
        <input class="configKey form-control" data-l1key="CT_motor_minupdate" placeholder="0.5"/>
      </div>
      <div class="col-xs-2">
        <span> {{secondes}}</span>
      </div>
    </div>
  </fieldset>
</form>