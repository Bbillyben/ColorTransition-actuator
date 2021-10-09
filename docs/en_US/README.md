# Color Transition_Actuator plugin for Jeedom

<p align="center">
  <img width="100" src="/plugin_info/ColorTransition_actuator_icon.png">
</p>

This plugin allows you to roll out color transitions from the <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a> plugin onto commands.

Colors are applicable through the `event` method of an info command, through message type commands (in the title or body of the post) or through color type commands. 
The plugin also allows to call a scenario with the color tag. 

You can configure several devices/commands, of different types, to be updated in a single device ColorTransform_actuator to synchronize transitions.

**Usage**, This plugin allows to:
1. Define a value of the cursor to update the 'actuators' by the calculation of the color by the ColorTransform plugin
2. Launch an automatic transition according to a defined timing


## Equipment 

>!! the equipment configuration will send an error if you don't have the plugin installed <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>

 ### General Settings      
 <p align="center">
  <img width="50%%" src="/plugin_info/img/equipment.png">
</p>

 * __Equipment Name__ 
 * __Parent Object__ 
 * __Category__ 
 Like any classic equipment

 ### ColorTransition equipment  
 
 this is an equipment of the plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>
 
 The list of available equipment shows the activated colortransform equipment.
 
 This Colortransition equipment will be used to calculate the transition color value, according to its parameters, except for the output format which are defined by 'activator' (alpha channel, white channel and hexadecimal or json format).
 
 ### Transition parameters
 
 * __Duration Move In__ : duration, in seconds, of the transition up (100%) or towards the target
 * __Duration Move Out__ : duration, in seconds, of the transition down (0%) or from the target to the current value
 * __Update Interval__ : Interval, in seconds, of the transition update. Don't go too low (<0.5) as this will slow down the transition, see the system!

## Commands

 Nine commands are created with the equipment: 
 * __Cursor__ : Info type numeric which contains the value of the cursor. The min and max limits are set from the limits defined in the ColorTransition device chosen
* __Set Cursor__ : Action type slider which allows to define the value of *Cursor* between the specified limits
 * Cursor Target__ : Numeric type info that contains the target value of the cursor. This value is removed at the end of the transition
* __Set Target__ : Action type slider which allows to define the value of *Target Cursor*.
* __Current Color__ : String type info that contains the current calculated value for the transition

* __Status__ : Binary type info that tells if the transition is currently moving (1/true : in progress, 0/false : stopped)

* __Move In__ : Action that allows to launch the transition, from the current value: 
  * up/100% or 
  * to the `target cursor` value if it is defined
* __Move Out__ : Action which allows to launch the transition, from the current value : 
  * down/0% or 
  * *From* the value of `Target Cursor` if it is defined to the current value of `Cursor`.
* __Stop__: Action that stops the transition

> Note:* the default templates applied to `Current Color`, `Set Cursor` and `Set Index` are those of the ColorTransform plugin

## Actuators

List of commands or scenarios to activate when updating transitions.
 <p align="center">
  <img width="100%" src="/plugin_info/img/actuator.png">
</p>

* __Name__: a unique name that you choose

**Command parameters:**

* __Type__ : the type of command to call : 
   * *Command action* : for a command of type Info or Action
   * *Scenario* : to call a scenario 
* __Command__ : to select a command or a scenario according to the __Type__ defined
* __Destination__ : allows to define how the color will be transmitted via the command, depending on what has been selected previously.
  * *event of the info command* : If the command is of type info
  * *scenario tags* : If the command is a scenario. The `#color#` tag will be filled with the scenario start
  * *Title* : If the command is a message type action command, the color will be sent via the title
  * *Message* : If the command is a message type action command, the color will be sent via the message body
  * *Color*: If the command is a color action command

**Color parameter:**
refers to the format parameters of the ColorTransform device (see [Doc](https://github.com/Bbillyben/ColorTransition/blob/master/README.md#sortie-couleurdoc))
* __Use Alpha Channel__ : If checked, the alpha channel will be added
* __Use White channel__ : If checked, the white channel will be added
* __Output Format__ : specify the output format 
  * *Hexadecimal* : format ``#AAWWRRGGBB`` or ``#AARRGGBB`` or ``#WWRRGGBB``` or ``#RRGGBB```.  
  * *json* : json type format : ``{"r":rr, "g":gg, "b":bb, "a":aa, "w":ww}``, with or without ``a`` and ``w`` channels, at least ``{"r":rr, "g":gg, "b":bb}``


## Plugin configuration
 <p align="center">
  <img width="100%" src="/plugin_info/img/configuration.png">
</p>
Two parameters are adjustable: 

* __Maximum engine execution time__: Allows you to set a maximum engine execution time, expressed in seconds. allows you to limit the max time for safety. Default is 7200 seconds => 2 hours!
* __Minimum time to update the transition__ : allows you to define the minimum time you can set in the equipment. Default is 0.5. Be careful, going down too low may make the transition slow down, or even the machine...!

