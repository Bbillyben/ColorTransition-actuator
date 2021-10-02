
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


/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
});

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
     var _cmd = {configuration: {}};
   }
   if (!isset(_cmd.configuration)) {
     _cmd.configuration = {};
   }

   if( _cmd.logicalId =='status' || _cmd.logicalId =='stop' || _cmd.logicalId =='currentColor' || _cmd.logicalId =='setCurseurTarget' || _cmd.logicalId =='curseurTarget' || _cmd.logicalId == 'move_in' || _cmd.logicalId == 'move_out' ||_cmd.logicalId == 'curseurIndex' || _cmd.logicalId == 'setCurseurIndex'  ){
      var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
      tr += '<td style="width:60px;">';
      tr += '<span class="cmdAttr" data-l1key="id"></span>';
      tr += '</td>';
     
      tr += '<td style="min-width:300px;width:350px;">';
      tr += '<div class="row">';
      tr += '<div class="col-xs-7">';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}">';
      tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display : none;margin-top : 5px;" title="{{Commande information liée}}">';
      tr += '<option value="">{{Aucune}}</option>';
      tr += '</select>';
      tr += '</div>';
      tr += '<div class="col-xs-5">';
      tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> {{Icône}}</a>';
      tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;"></span>';
      tr += '</div>';
      tr += '</div>';
      tr += '</td>';
      tr += '<td>';
      tr += '<span class="type" type="' + init(_cmd.type) + '">' +  init(_cmd.type) + '</span>/';
      tr += '<span class="" subType="' + init(_cmd.subType) + '">' + init(_cmd.subType) + '</span>';
      //tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
      //tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
      tr += '</td>';
      /*tr += '<td style="min-width:150px;width:350px;">';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min.}}" title="{{Min.}}" style="width:30%;display:inline-block;"/> ';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max.}}" title="{{Max.}}" style="width:30%;display:inline-block;"/> ';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" title="{{Unité}}" style="width:30%;display:inline-block;"/>';
      tr += '</td>';*/
      tr += '<td style="min-width:80px;width:350px;">';
      tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label>';
      //tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label>';
      //tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label>';
      tr += '</td>';
      tr += '<td style="min-width:80px;width:200px;">';
      if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>';
      }
      tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
      tr += '</tr>';
      $('#table_cmd tbody').append(tr);
      var tr = $('#table_cmd tbody tr').last();
  }else{

      var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
      tr += '<td style="width:60px;">';
      tr += '<span class="cmdAttr" data-l1key="id"></span>';
      tr += '<input class="cmdAttr form-control input-sm type" data-l1key="type" value="info" style="display:none;"/>';
      tr += '<span class="subType" subType="string" value="string"  style="display:none;"></span>';
      tr += '</td>';
      //Nom
      tr += '<td style="min-width:15px;width:30px;">';
      tr += '<div class="row">';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}">';
      tr += '</div>';
      tr += '</td>';
      //Type
      tr += '<td style="min-width:50px;width:150px;">';
      tr += '<div class="col-xm-5">';
      tr += '<select id="sel_object" class="cmdAttr form-control" data-l1key="configuration" data-l2key="act_type">';
      tr += '<option value="action">{{Commande Action}}</option>';
      tr += '<option value="scenario">{{Scenario}}</option>';
      tr += '</select>';
      tr += '</div>';
      tr += '</td>';

      //commande
      tr += '<td style="width:260px;">';
        // type scenario
      tr += '<div class="input-group" style="">';
      tr += '<input class="cmdAttr form-control CTA-scenar-el" data-l1key="configuration" data-l2key="sendScenar"/>';
      // type action
      tr += '<input class="cmdAttr form-control CTA-cmd-el" data-l1key="configuration" data-l2key="sendCmd"/>';
      tr += '<span class="input-group-btn">';
      tr += '<button type="button" class="btn btn-default cursor listCmdActionMessage tooltips cmdSendSel" title="{{Rechercher une commande}}" data-input="sendCmd"><i class="fas fa-list-alt"></i></button>';
      tr += '</span>';
      tr += '</div>';
    
      tr += '</td>';
    // destination
    tr += '<td style="width:160px;">';
    
    tr += '<div class="dest-scenar">';
    tr += '<span>{{tags du scenario}}</span>';
    tr += '</div>';
    tr += '<div class="dest-info">';
    tr += '<span>{{event de la commande info}}</span>';
    tr += '</div>';
    tr += '<div class="dest-cmd">';
    tr += '<select class="cmdAttr form-control" data-l1key="configuration" data-l2key="dest">';
    //tr += '<option value="value">{{Valeur (par défaut)}}</option>';
    tr += '<option value="title">{{titre}}</option>';
    tr += '<option value="message">{{message}}</option>';
    tr += '<option value="color">{{Couleur}}</option>';
    tr += '</select>';
    tr += '</div>';
    tr += '</td>';
      //Options
      tr += '<td style="width:100px;">';
    	// use alpha
     tr += '<div class="input-group">';
    	tr += '<input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="use_alpha" checked="">use alpha</input>';
     tr += '</div>';
    	//use white
    tr += '<div class="input-group">';
    tr += '<input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="use_white">use White</input>';
     tr += '</div>';
      tr += '</td>';
    
    // type de sortie
      tr += '<td style="min-width:80px;width:200px;">';
    
    tr += '<select id="cursor-range" class="cmdAttr form-control" data-l1key="configuration" data-l2key="color-format">';
    tr += '<option value="hexa">Hexadecimale - #(AA/WW)RRGGBB</option>';
    tr += '<option value="json">json  - {"r":rr,"g":gg,"b":bb,"a":aa, "w":ww})</option>';
    tr += '</select>';
    
    tr += '</td>';
      //Actions
      tr += '<td style="min-width:80px;width:200px;">';
      tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
      tr += '</tr>';
      $('#table_actuator_cmd tbody').append(tr);
      var tr = $('#table_actuator_cmd tbody tr').last();
      

  }


 // pour récupe la select de commande
 
 $(".cmdSendSel").on('click', function () {
   
 var el = $(this);
  var elType = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=act_type]');
 if(elType.val()=='action'){
     jeedom.cmd.getSelectModal(null, function(result) {
       var calcul = el.closest('div').find('.cmdAttr[data-l1key=configuration][data-l2key=sendCmd]');
       calcul.val('');
       calcul.atCaret('insert', result.human);
       makeDestinationSelection(el,result.cmd.type);
     });
 }else{
     jeedom.scenario.getSelectModal(null, function(result) {
     var calcul = el.closest('div').find('.cmdAttr[data-l1key=configuration][data-l2key=sendScenar]');
       calcul.val('');
     calcul.atCaret('insert', result.human);
     makeDestinationSelection(el,'scenario');
   });
 }
});

$('.cmdAttr[data-l1key=configuration][data-l2key=sendCmd]').on('change click',function(){
  	computeDestinationSelection($(this),null);
});
$('.cmdAttr[data-l1key=configuration][data-l2key=act_type]').on('change click',function(){
  //console.log('l2key=act_type event :'+$(this).val());
  var elType = $(this).closest('tr');
  if($(this).val()=='action'){
    elType.find('.CTA-scenar-el').hide();
    elType.find('.CTA-cmd-el').show();
    var typeSel=null;
  }else{
    elType.find('.CTA-scenar-el').show();
    elType.find('.CTA-cmd-el').hide(); 
    var typeSel=$(this).val();
  }
  
  computeDestinationSelection($(this),typeSel);
});
  
function computeDestinationSelection(el, typeSel){
  var elType = el.closest('tr');
  if(typeSel == null){
    var idCmd=elType.find('.CTA-cmd-el').val();//.replace(new RegExp('#', 'g'),''));
    if(idCmd==''){
      	var sel = elType.find('.cmdAttr[data-l2key="act_type"]').val();
      	makeDestinationSelection(el,sel);
     return;
    }
    var cmdSend = jeedom.cmd.byHumanName({
			humanName: idCmd,
			success:  function(result) {
                    makeDestinationSelection(el,result.type);
                  },
            error:function(err){
              console.log("error :"+JSON.stringify(err));
              makeDestinationSelection(el,'action');
            }
		});
  }else{
  	makeDestinationSelection(el,typeSel);
  }
};
  
function makeDestinationSelection(el, typeSel){
  var elType = el.closest('tr');
  switch (typeSel) {
    case 'scenario':
      elType.find('.dest-scenar').show();
      elType.find('.dest-cmd').hide();
      elType.find('.dest-info').hide();
      break;
    case 'action':
    case null:
      elType.find('.dest-scenar').hide();
      elType.find('.dest-cmd').show();
      elType.find('.dest-info').hide();
      break;
    case 'info':
      elType.find('.dest-scenar').hide();
      elType.find('.dest-cmd').hide();
      elType.find('.dest-info').show();
      break;
  }

};
   jeedom.eqLogic.builSelectCmd({
     id:  $('.eqLogicAttr[data-l1key=id]').value(),
     filter: {type: 'info'},
     error: function (error) {
       $('#div_alert').showAlert({message: error.message, level: 'danger'});
     },
     success: function (result) {
       tr.find('.cmdAttr[data-l1key=value]').append(result);
       tr.setValues(_cmd, '.cmdAttr');
       jeedom.cmd.changeType(tr, init(_cmd.subType));
       tr.find('.cmdAttr[data-l2key=act_type]').trigger("change");
     }
   });


 };