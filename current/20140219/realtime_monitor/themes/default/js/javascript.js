var module_name = 'realtime_monitor';

var estadoCliente = null;
var estadoClienteHash = null;

// Param for queue timestamp
var queueNumber = null;

// Objeto de timer para el cronómetro
var timer = null;

//Objeto EventSource, si está soportado por el navegador
var evtSource = null;

//Inicializar estado del cliente al refrescar la página
function initialize_client_state(nuevoEstado, nuevoEstadoHash)
{		
	estadoCliente = nuevoEstado;
	estadoClienteHash = nuevoEstadoHash;	
	var fechaInicio = new Date();
	
	for (var k in estadoCliente) {
		var keys = ['sec_laststatus','sec_calls'];
		for (var j = 0; j < keys.length; j++) {
			var ktimestamp = keys[j];
			estadoCliente[k]['orig_'+ktimestamp] = estadoCliente[k][ktimestamp];
			if (estadoCliente[k][ktimestamp] != null) {
				var d = new Date();
				d.setTime(fechaInicio.getTime() - estadoCliente[k][ktimestamp] * 1000);
				estadoCliente[k][ktimestamp] = d;
			}
		}	
	}	
    setTimeout(do_checkstatus, 1);
	setTimeout(queue_waiting_checkstatus,1);	
	timer = setTimeout(actualizar_cronometro, 1);
}

$(window).unload(function() {
	if (evtSource != null) {
		evtSource.close();
		evtSource = null;
	}
});

//Cada 500 ms se llama a esta función para actualizar el cronómetro
function actualizar_cronometro()
{
	actualizar_valores_cronometro();
	timer = setTimeout(actualizar_cronometro, 500);
}

function actualizar_valores_cronometro()
{
	var totalesCola = {};	
	for (var k in estadoCliente) {		
		// El último estado se actualiza si el tiempo no es nulo
		if (estadoCliente[k]['sec_laststatus'] != null) {
			formatoCronometro('#'+k+'-sec_laststatus', estadoCliente[k]['sec_laststatus']);
		}			
		// El tiempo total de llamadas se actualiza si el estado es oncall y si
		// está activa la bandera oncallupdate
		if (estadoCliente[k]['agentstatus'] == 'oncall' && estadoCliente[k]['oncallupdate']) {
			formatoCronometro('#'+k+'-sec_calls', estadoCliente[k]['sec_calls']);
		} 
	}	
}

function formatoCronometro(selector, fechaInicio)
{
	var fechaDiff = new Date();
	var msec = fechaDiff.getTime() - fechaInicio.getTime();	
	formatoMilisegundo(selector, msec);	
	return msec;	
}

function formatoMilisegundo(selector, msec)
{	
	var tiempo = [0, 0, 0];
	tiempo[0] = (msec - (msec % 1000)) / 1000;
	tiempo[1] = (tiempo[0] - (tiempo[0] % 60)) / 60;
	tiempo[0] %= 60;
	tiempo[2] = (tiempo[1] - (tiempo[1] % 60)) / 60;
	tiempo[1] %= 60;
	var i = 0;
	for (i = 0; i < 3; i++) { if (tiempo[i] <= 9) tiempo[i] = "0" + tiempo[i]; }
	$(selector).text(tiempo[2] + ':' + tiempo[1] + ':' + tiempo[0]);		
}

function queue_waiting_checkstatus()
{
	var params = {
			menu:		module_name, 
			rawmode:	'yes',
			action:		'queueWaitingStatus',						
		};

	if (window.EventSource) {
		params['serverevents'] = true;
		evtSource = new EventSource('index.php?' + $.param(params));
		evtSource.onmessage = function(event) {
			updateQueueWaiting($.parseJSON(event.data));
			//console.log(event.data);
		}
	} else {
		$.post('index.php?menu=' + module_name + '&rawmode=yes', params,
		function (respuesta) {
			updateQueueWaiting(respuesta);						
			//console.log(respuesta);
			setTimeout(queue_waiting_checkstatus, 1);
		});
	}
}

//update queue waiting when have new update
function updateQueueWaiting(json)
{	
    var fechaInicio = new Date();
	queueNumber = json;
	$('#queue-waiting').empty();
	content  = '<tr><th>Khách hàng</th><th>Thời gian chờ</th><th>Queue</th></tr>';
	for (var i = 0; i < json.length; i++) {
        content  += '<tr class="vip">';
		content  += "<td>" + json[i].phone_number + "</td>";
		content  += '<td id="'+i+'-queue_wait_timestamp">' + json[i].wait_time + '</td>';
		content  += "<td>" + json[i].queue + "</td>";
		content  += '</tr>';
		//time auto count up
		var d = new Date();
		d.setTime(fechaInicio.getTime() - json[i].wait_time * 1000);
		queueNumber[i]['wait_time'] = d;				
	}
	$('#queue-waiting').append(content);	
	queue_waiting_auto_update();
}

function queue_waiting_count_time()
{
	// run if there is queue waiting in list //tri	
	for (var i = 0; i < queueNumber.length; i++) {		
		if (queueNumber[i]['wait_time'] != null) {
			formatoCronometro('#'+i+'-queue_wait_timestamp', queueNumber[i]['wait_time']);		
		}
	}		
}

function queue_waiting_auto_update()
{
	queue_waiting_count_time();
	timer = setTimeout(queue_waiting_auto_update, 1000);
}

function do_checkstatus()
{
	var params = {
			menu:		module_name, 
			rawmode:	'yes',
			action:		'checkStatus',			
			clientstatehash: estadoClienteHash
		};	
	
	if (window.EventSource) {
		params['serverevents'] = true;
		evtSource = new EventSource('index.php?' + $.param(params));
		evtSource.onmessage = function(event) {
			//console.log($.parseJSON(event.data));			
			if ($.parseJSON(event.data) != null) 
				manejarRespuestaStatus($.parseJSON(event.data));
		}
	} else {
		$.post('index.php?menu=' + module_name + '&rawmode=yes', params,
		function (respuesta) {	
            //console.log(respuesta);
			if (respuesta != null)
				manejarRespuestaStatus(respuesta);
			setTimeout(do_checkstatus, 1);
		});
	}
}

function manejarRespuestaStatus(respuesta)
{
	if (respuesta == null)
		return;
	var fechaInicio = new Date();
	var keys = ['sec_laststatus', 'sec_calls'];
	
	// Intentar recargar la página en caso de error
	if (respuesta['error'] != null) {
		window.alert(respuesta['error']);
		location.reload();
		return;
	}
	
	for (var k in respuesta) {
		if (k == 'estadoClienteHash') {
			// Caso especial - actualizar hash de estado
			if (respuesta[k] == 'mismatch') {
				// Ha ocurrido un error y se ha perdido sincronía
				location.reload();
				return;
			} else {
				estadoClienteHash = respuesta[k];
			}
		} else if (estadoCliente[k] != null) {
			if (estadoCliente[k]['agentstatus'] != respuesta[k]['agentstatus']) {
				// El estado del agente ha cambiado, actualizar icono
				var statuslabel = $('#'+k+'-statuslabel');
				statuslabel.empty();
				switch (respuesta[k]['agentstatus']) {
				case 'offline':		
					$('#'+k).attr('class','item invisible');
					statuslabel.text('Offline'); // TODO: i18n
					$('#'+k).attr('data-status',respuesta[k]['agentstatus']);					
					$('#'+k+'-image').attr('src','modules/realtime_monitor/themes/default/images/status_offline-ic.png');
					$('#'+k+'-image').attr('title',k+': Chưa sẵn sàng');
					$('#'+k+'-customer').text('(not on call)');
					// logout button handle
					$('#'+k+'-logout_btn').hide();					
					// note button handle
					$('#'+k+'-addnote_btn').hide();
					// spycall button handle
					$('#'+k+'-spycall_link').attr('href','');
					$('#'+k+'-spycall_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_disable-ic.png');
					// spycall whisper button hanlde
					$('#'+k+'-spycall_whisper_link').attr('href','');
					$('#'+k+'-spycall_whisper_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_2_disable-ic.png');
					// hangoff button handle
					$('#'+k+'-hangoff_link').attr('href','');
					$('#'+k+'-hangoff_btn').attr('src','modules/realtime_monitor/themes/default/images/hang_off_disable-ic.png');
					// pick call button handle
					$('#'+k+'-pickcall_link').attr('href','');
					$('#'+k+'-pickcall_btn').attr('src','modules/realtime_monitor/themes/default/images/change_disable-ic.png');					
					break;
				case 'online':
					$('#'+k).attr('class','item');
					statuslabel.text('Online');
					$('#'+k).attr('data-status',respuesta[k]['agentstatus']);
					$('#'+k+'-image').attr('src','modules/realtime_monitor/themes/default/images/status_free-ic.png');
					$('#'+k+'-image').attr('title',k+': Đang sẵn sàng');					
					$('#'+k+'-customer').text('(not on call)');
					// button logout handle
					$('#'+k+'-logout_btn').show();
					$('#'+k+'-logout_btn').attr('src','modules/realtime_monitor/themes/default/images/logout-ic.png');
					$('#'+k+'-logout_btn').attr('title','Kết thúc phiên làm việc của tổng đài viên');
					// note button handle
					$('#'+k+'-addnote_btn').hide();					
					// spycall button handle
					$('#'+k+'-spycall_link').attr('href','');
					$('#'+k+'-spycall_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_disable-ic.png');
					// spycall whisper button hanlde
					$('#'+k+'-spycall_whisper_link').attr('href','');
					$('#'+k+'-spycall_whisper_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_2_disable-ic.png');
					// hangoff button handle
					$('#'+k+'-hangoff_link').attr('href','');
					$('#'+k+'-hangoff_btn').attr('src','modules/realtime_monitor/themes/default/images/hang_off_disable-ic.png');
					// pick call butoon handle
					$('#'+k+'-pickcall_link').attr('href','');
					$('#'+k+'-pickcall_btn').attr('src','modules/realtime_monitor/themes/default/images/change_disable-ic.png');					
					break;
				case 'oncall':
					statuslabel.text('Đang gọi:');
					$('#'+k).attr('data-status',respuesta[k]['agentstatus']);
					$('#'+k+'-image').attr('src','modules/realtime_monitor/themes/default/images/status_on_call-ic.png');
					$('#'+k+'-image').attr('title',k+': Đang gọi - Tại kênh: ' + respuesta[k]['linkqueue']);
					$('#'+k+'-customer').text(respuesta[k]['customer']);
					// button logout handle
					$('#'+k+'-logout_btn').show();
					$('#'+k+'-logout_btn').attr('src','modules/realtime_monitor/themes/default/images/logout-ic.png');					
					$('#'+k+'-logout_btn').attr('title','Kết thúc phiên làm việc của tổng đài viên');
					// note button handle
					$('#'+k+'-addnote_btn').show();
					$('#'+k+'-addnote_btn').attr('src','modules/realtime_monitor/themes/default/images/note-ic.png');
					$('#'+k+'-addnote_btn').attr('title','Ghi chú cuộc gọi này'); 
					// spycall button handle
					$('#'+k+'-spycall_link').attr('href','#');
					$('#'+k+'-spycall_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping-ic.png');
					// spycall whisper button hanlde
					$('#'+k+'-spycall_whisper_link').attr('href','#');
					$('#'+k+'-spycall_whisper_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_2-ic.png');
					// hangoff button handle
					$('#'+k+'-hangoff_link').attr('href','#');
					$('#'+k+'-hangoff_btn').attr('src','modules/realtime_monitor/themes/default/images/hang_off-ic.png');
					// pick call butoon handle
					$('#'+k+'-pickcall_link').attr('href','#');
					$('#'+k+'-pickcall_btn').attr('src','modules/realtime_monitor/themes/default/images/change-ic.png');					
					break;
				case 'paused':
					statuslabel.text('Tạm nghỉ:');
					$('#'+k).attr('data-status',respuesta[k]['agentstatus']);
					$('#'+k+'-image').attr('src','modules/realtime_monitor/themes/default/images/status_away-ic.png');
					$('#'+k+'-image').attr('title',k+': Đang tạm nghỉ');					
					$('#'+k+'-customer').text('(not on call)');
					// button logout handle
					$('#'+k+'-logout_btn').show();
					$('#'+k+'-logout_btn').attr('src','modules/realtime_monitor/themes/default/images/logout-ic.png');
					$('#'+k+'-logout_btn').attr('title','Kết thúc phiên làm việc của tổng đài viên');
					// note button handle
					$('#'+k+'-addnote_btn').hide();					
					// spycall button handle
					$('#'+k+'-spycall_link').attr('href','');
					$('#'+k+'-spycall_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_disable-ic.png');
					// spycall whisper button hanlde
					$('#'+k+'-spycall_whisper_link').attr('href','');
					$('#'+k+'-spycall_whisper_btn').attr('src','modules/realtime_monitor/themes/default/images/wiretapping_2_disable-ic.png');
					// hangoff button handle
					$('#'+k+'-hangoff_link').attr('href','');
					$('#'+k+'-hangoff_btn').attr('src','modules/realtime_monitor/themes/default/images/hang_off_disable-ic.png');
					// pick call butoon handle
					$('#'+k+'-pickcall_link').attr('href','');
					$('#'+k+'-pickcall_btn').attr('src','modules/realtime_monitor/themes/default/images/change_disable-ic.png');					
					break;
				}
				estadoCliente[k]['agentstatus'] = respuesta[k]['agentstatus'];
			}
			
			// Actualizar los cronómetros con los nuevos valores
			for (var j = 0; j < keys.length; j++) {
				var ktimestamp = keys[j];
				estadoCliente[k]['orig_'+ktimestamp] = respuesta[k][ktimestamp];
				if (respuesta[k][ktimestamp] == null) {
					estadoCliente[k][ktimestamp] = null;
					$('#'+k+'-'+ktimestamp).empty();
				} else {
					var d = new Date();
					d.setTime(fechaInicio.getTime() - respuesta[k][ktimestamp] * 1000);
					estadoCliente[k][ktimestamp] = d;
					formatoCronometro('#'+k+'-'+ktimestamp, estadoCliente[k][ktimestamp]);
				}
			}
			estadoCliente[k]['oncallupdate'] = respuesta[k]['oncallupdate'];
			estadoCliente[k]['num_calls'] = respuesta[k]['num_calls'];
			$('#'+k+'-num_calls').text(estadoCliente[k]['num_calls']);
		} else {
			// TODO: no se maneja todavía aparición de agente en nueva cola
		}
	}
	
	// Actualizar número de llamadas por cola
	var totalesCola = {};
	for (var k in estadoCliente) {
		var kq = estadoCliente[k]['queuetotal'];
		if (kq != null) {
			if (totalesCola[kq] == null) {
				totalesCola[kq] = estadoCliente[k]['num_calls'];
			} else {
				totalesCola[kq] += estadoCliente[k]['num_calls'];
			}
		}
	}
	for (var kq in totalesCola) {
		$('#'+kq+'-num_calls').text(totalesCola[kq]);
	}
	
	// Actualizar los totales de tiempo por cola
	actualizar_valores_cronometro();
}
