# Plugin de Transición_Actuador de Color para Jeedom

<p align="center">
  <img width="100" src="/plugin_info/ColorTransition_actuator_icon.png">
</p>

Este plugin permite desplegar transiciones de color desde el plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a> en comandos.

Los colores son aplicables a través del método `event` de un comando de información, a través de comandos de tipo mensaje (en el título o en el cuerpo del mensaje) o a través de comandos de tipo color. 
El plugin también permite llamar a un escenario con la etiqueta de color. 

Puede configurar varios dispositivos/comandos, de diferentes tipos, para que se actualicen en un único dispositivo ColorTransform_actuator para sincronizar las transiciones.

**Uso**, Este plugin permite:
1. Definir un valor del cursor para actualizar los 'actuadores' calculando el color por el plugin ColorTransform
2. Lanzar una transición automática según una temporización definida


## Equipo 

> !!! la configuración del equipo enviará un error si no tienes el plugin instalado <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>

 ### Ajustes generales      
 <p align="center">
  <img width="50%%" src="/plugin_info/img/equipamiento.png">
</p>

 * __Nombre del equipo__ 
 * __Objeto padre__ 
 * _Categoría__ 
 Como cualquier equipo clásico

 ### ColorTransition Equipment  
 
 este es un equipo del plugin <a href='https://github.com/Bbillyben/ColorTransition' target='_blank' >ColorTransition <img width="20" src="https://github.com/Bbillyben/ColorTransition/blob/master/plugin_info/ColorTransition_icon.png"> </a>
 
 La lista de equipos disponibles muestra los equipos de transformación de color activados.
 
 Este equipo de Colortransition se utilizará para calcular el valor del color de transición, de acuerdo con sus parámetros, excepto el formato de salida que son definidos por 'activador' (canal alfa, canal blanco y formato hexadecimal o json).
 
 ### Parámetros Transición
 
 * __Duración Move In__ : duración, en segundos, de la transición hacia arriba (100%) o hacia el objetivo
 * __Duration Move Out__ : duración, en segundos, de la transición hacia abajo (0%) o desde el objetivo hasta el valor actual
 * __Intervalo de actualización__ : Intervalo, en segundos, de actualización de la transición. No baje demasiado (<0,5) ya que esto ralentizará la transición, ¡vea el sistema!

## Comandos

 Se crean once comandos con el equipo: 
 * __Cursor__ : Información de tipo numérico que contiene el valor del cursor. Los límites mínimo y máximo se establecen a partir de los límites definidos en el dispositivo ColorTransition elegido
* __Set Cursor__: Deslizador de tipo acción que permite definir el valor de *Cursor* entre los límites especificados
 * Cursor Target__ : Información de tipo numérico que contiene el valor de destino del cursor. Este valor se elimina al final de la transición
* __Set Target__: Deslizador de tipo acción que permite definir el valor de *Target Cursor*.
* __Current Color__: Información de tipo cadena que contiene el valor calculado actualmente para la transición

* __Status__: Información de tipo binario que indica si la transición se está moviendo (1/true: en progreso, 0/false: detenido)

* __Move In__ : Acción que permite lanzar la transición, a partir del valor actual: 
  * hacia arriba/100% o 
  * al valor del `cursor objetivo` si está definido
* __Move Out__ : Acción que permite lanzar la transición, partiendo del valor actual: 
  * hacia abajo/0% o 
  * *Desde* el valor del `Cursor de destino` si se establece en el valor actual del `Cursor
* __Stop__: Acción que detiene la transición
* __Bucle infinito__ : Acción que permite lanzar un bucle, siempre iniciado hacia arriba (100% o objetivo), entre la posición actual del cursor y el 100% o el objetivo, y que se detendrá sólo en la llamada a ``Stop````'' o en el tiempo máximo de ejecución del motor
* __Bucle__ : Acción de subtipo mensaje, que permite iniciar un bucle, siempre iniciado hacia arriba (100% u objetivo), entre la posición actual del cursor y el 100% o el objetivo, que se detendrá después de n iteraciones definidas en el título o el cuerpo del mensaje.


> Nota:* las plantillas por defecto aplicadas a `Color actual`, `Set Cursor` y `Set Index` son las del plugin ColorTransform

## Actuadores

Lista de comandos o escenarios a activar al actualizar las transiciones.
 <p align="center">
  <img width="100%" src="/plugin_info/img/actuator.png">
</p>

* __Name__: un nombre único que usted elija

**Parámetro de comando:**

* __Tipo__ : el tipo de comando que se va a llamar : 
   *Comando acción* : para un comando de tipo Info o Acción
   * *Escenario* : para llamar a un escenario 
* __Comando__ : para seleccionar un comando o un escenario según el __Tipo__ definido
* __Destino__ : permite definir cómo se transmitirá el color a través del comando, en función de lo que se haya seleccionado previamente.
  *Evento del comando info* : Si el comando es de tipo info
  *Etiquetas de escenario*: Si el comando es un escenario. La etiqueta `#color#` se rellenará con el inicio del escenario
  *Title*: Si el comando es un comando de acción de tipo mensaje, el color se enviará a través del título
  *Mensaje*: Si el comando es un comando de Acción de Mensaje, el color se enviará a través del cuerpo del mensaje
  *Color* : Si el comando es un comando de acción de color

**Parámetro de color:**
se refiere a la configuración del formato del equipo ColorTransform (véase [Doc](https://github.com/Bbillyben/ColorTransition/blob/master/README.md#sortie-couleurdoc))
* Usar el canal alfa__ : Si se marca, se añadirá el canal alfa
* Usar el canal blanco__ : Si se marca, se añadirá el canal blanco
* __Formato de salida__ : especifica el formato de salida 
  *Hexadecimal* : formato ``#AAWWRRGGBB`` o ``#AARRGGBB`` o ``#WRRGGBB`` o ``#RGGBB`` *json* : formato tipo ``AWRRGGBB`` o ``AARRGGBB`` o ``#WRRGGBB`` o ``#RGGBB``.  
  * *json*: formato tipo json: ``{"r":rr, "g":gg, "b":bb, "a":aa, "w":ww}``, con o sin canales ``a`` y ``w``, al menos ``{"r":rr, "g":gg, "b":bb}``.


## Configuración del plugin
 <p align="center">
  <img width="100%" src="/plugin_info/img/configuration.png">
</p>
Se pueden ajustar dos parámetros: 

* _Tiempo máximo de ejecución del motor__: Permite establecer un tiempo máximo de ejecución del motor, expresado en segundos. permite limitar el tiempo máximo por seguridad. El valor por defecto es de 7200 segundos => 2 horas.
* __Tiempo mínimo de actualización de la transición__ : permite definir el tiempo mínimo que se puede definir en el equipo. Por defecto es 0,5. Cuidado, bajar demasiado puede provocar la ralentización de la transición, ¡o incluso de la máquina!
* __Apagado de emergencia del motor__ : detener todas las transiciones y purgar la memoria, si es necesario.
* __Data Current Engine__ : Los datos utilizados por el motor actual
