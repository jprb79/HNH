{* Este DIV se usa para mostrar los mensajes de éxito *}
<div
    id="elastix-callcenter-info-message"
    class="ui-state-highlight ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-info-message-text"></span>
    </p>
</div>
{* Este DIV se usa para mostrar los mensajes de error *}
<div
    id="elastix-callcenter-error-message"
    class="ui-state-error ui-corner-all">
    <p>
        <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-error-message-text"></span>
    </p>
</div>

<div class="container">
	<div class="content-left">
		<div id="filter">
			<div>
				{*
                <div id="searchBox" style>
					<input type="text" id="txt_search_agent" placeholder="Tìm kiếm theo số Agent..." />
					<a id="search_agent_Btn" href="javascript:void(0)"><img src="{$THEME_PATH}/images/search-ic.png" /></a>
				</div>
                *}
				<div id="supervisorExtension">
					<label>Số Extension: </label>&nbsp
					<input type="text" id="txt_supervisor_ext" placeholder="Nhập Extension" value="{$SUPERVISOR_NUMBER}" style="text-align:right;"/>					
				</div>
				<ul id="selector">
					<li id="status-selector" data-toggle="status-filter"><a href="javascript:void(0)">Trạng thái</a></li>
					<li id="time-selector" data-toggle="time-sort"><a href="javascript:void(0)">Thời gian</a></li>
					<li id="call-selector" data-toggle="call-sort"><a href="javascript:void(0)">Cuộc gọi</a></li>
				</ul>
			</div>
			<ul class="filter" id="status-filter">
				<li id="filter-free" data-filter="online"><a href="javascript:void(0)"><img src="{$THEME_PATH}/images/status_free-ic.png" alt="free" height="23px" /> Online</a></li>
				<li id="filter-away" data-filter="paused"><a href="javascript:void(0)"><img src="{$THEME_PATH}/images/status_away-ic.png" alt="away" height="23px" /> Paused</a></li>
				<li id="filter-oncall" data-filter="oncall"><a href="javascript:void(0)"><img src="{$THEME_PATH}/images/status_on_call-ic.png" alt="oncall" height="23px" /> On-call</a></li>
				<li id="filter-offline" data-filter="offline"><a href="javascript:void(0)"><img src="{$THEME_PATH}/images/status_offline-ic.png" alt="offline" height="23px" /> Offline</a></li>
				<li id="filter-reset" data-filter=""><a href="javascript:void(0)"><img src="{$THEME_PATH}/images/filter_reset.png" alt="offline" height="23px" /> All</a></li>
			</ul>
			<ul class="sort" id="time-sort" data-sort="totaltime">
				<li class="asc"><a href="javascript:void(0)">Tăng dần</a></li>
				<li class="desc"><a href="javascript:void(0)">Giảm dần</a></li>
			</ul>
			<ul class="sort" id="call-sort" data-sort="totalcall">
				<li class="asc"><a href="javascript:void(0)">Tăng dần</a></li>
				<li class="desc"><a href="javascript:void(0)">Giảm dần</a></li>
			</ul>
		</div>
		<div id="agent_info" class="list-item">
		{foreach from=$AGENT_STATUS key=AGENT_KEY item=AGENT name=item}			
			{if $smarty.foreach.item.index < $ITEM_LIMIT}
			<div id={$AGENT_KEY} class="{if $AGENT.agentstatus eq 'offline'}item invisible{else}item{/if}" data-status="{$AGENT.agentstatus}">
				<div class="item-info">
					<div class="item-left">
						<img id="{$AGENT_KEY}-image" class="status" src="{$AGENT.img_status}" title="{$AGENT.img_status_title}"/>												
						<a href="javascript:void(0)"><img id="{$AGENT_KEY}-logout_btn" src="{$THEME_PATH}/images/logout-ic.png" title="Kết thúc phiên làm việc của tổng đài viên" onclick="do_logout('{$AGENT.agent_number}')" {if $AGENT.agentstatus eq 'offline'}hidden{/if}></a>
						{*
						<a class="note" href="javascript:void(0)"><img id="{$AGENT_KEY}-addnote_btn" src="{$THEME_PATH}/images/note-ic.png" title="Ghi chú cuộc gọi này" onclick="get_addnote('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}')" {if $AGENT.agentstatus ne 'oncall'}hidden{/if}/></a>*}																											
						<table class="detail">
							<tr>
								<th>Agent:</th>
								<td>{$AGENT.agentname}</td>
							</tr>
							<tr>
								<th><span id="{$AGENT_KEY}-statuslabel">{$AGENT.status_time_label}</span></th>
								<td><span id="{$AGENT_KEY}-sec_laststatus">{$AGENT.status_time}</span></td>
							</tr>
							<tr>
								<th>Khách hàng:</th>
								<td><span id="{$AGENT_KEY}-customer">{if $AGENT.agentstatus eq 'oncall'}{$AGENT.customer}{else}(not on call){/if}</span></td>
							</tr>
						</table>
					</div>
					<div class="item-right">
						<h4>Tổng call</h4>
						<h1 class="total-call"><span id="{$AGENT_KEY}-num_calls">{$AGENT.num_calls}</span></h1>
					</div>
				</div>
				<div class="item-buttons">
					<a id="{$AGENT_KEY}-spycall_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-spycall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping-ic.png {else}{$THEME_PATH}/images/wiretapping_disable-ic.png{/if}" title="Nghe xen không tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}',false)"/></a>
					<a id="{$AGENT_KEY}-spycall_whisper_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-spycall_whisper_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping_2-ic.png {else}{$THEME_PATH}/images/wiretapping_2_disable-ic.png{/if}" title="Nghe xen có tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}',true)"/></a>
					<a id="{$AGENT_KEY}-hangoff_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-hangoff_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/hang_off-ic.png {else}{$THEME_PATH}/images/hang_off_disable-ic.png{/if}" title="Ngắt cuộc gọi này" onclick="do_hangup('{$AGENT.agent_number}')"/></a>					
					<a id="{$AGENT_KEY}-pickcall_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-pickcall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/change-ic.png {else}{$THEME_PATH}/images/change_disable-ic.png{/if}" title="Lấy cuộc gọi này" onclick="do_transfer('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}')"/></a>										
					<p class="timer" title="Tổng thời gian nghe máy"><span id="{$AGENT_KEY}-sec_calls">{$AGENT.sec_calls_time}</span></p>
				</div>
			</div><!-- end item -->				
			{/if}
		{/foreach}		
		</div> <!-- list item -->
		<div class="pagination">
			<a class="btn" id="loadmore" href="javascript:void(0)" style="font-size:10pt;">Xem hết</a>
			<a class="btn" id="remove" href="javascript:void(0)" style="font-size:10pt;display: none;">Làm mới</a>
		</div>
	</div>
	<div class="content-right">
		{*
        <div id="searchBox">
			<input type="text" id="txt_search_ticket" placeholder="Tìm kiếm theo số khách hàng..." />
			<a id="btn_search_ticket" href="javascript:void(0)"><img src="{$THEME}/images/search-ic.png" /></a>
		</div> *}
		<div class="request-list">
			<div class="title">
				<h3>DANH SÁCH HÀNG ĐỢI</h3>
			</div>
			<div id="queue">					
					<div class="panel">
						<table id="queue-waiting">
							<tr>
								<th>Khách hàng</th>
								<th>Thời gian chờ</th>
								<th>Queue</th>
							</tr>
							{* no need because javascript will initialize
							{foreach from=$QUEUE_WAITING key=NUMBER item=QUEUE}
							<tr class="vip">
								<td>{$QUEUE.phone_number}</td>
								<td id="{$NUMBER}-timestamp">{$QUEUE.wait_time}</td>
								<td>{$QUEUE.queue}</td>
								<td>VIP</td>
							</tr>
							{/foreach}								
							*}
						</table>					
					</div>				
			</div>
		</div>
		<div class="request-list">
			<div class="title">
				<h3 style="float: left;">DANH SÁCH CUỘC GỌI GẦN NHẤT</h3>
				<a href="javascript:void(0)" id="refresh-list" onclick="do_refresh_call_history()"><img src="{$THEME_PATH}/images/btn-refresh.png"/></a>
			</div>
			<div id="tabs">				
				<div id="paylink-panel" class="panel"></div>
				<div id="mobivi-panel" class="panel">
					<table id="ticket-list">
						<tr>
							<th>Khách hàng</th>
							<th>Ngày gọi</th>
							<th>NV nghe máy</th>
							<th>Tình trạng</th>
							<th>Nội dung</th>
							<th>Giao vé</th>
						</tr>
					{foreach from=$CALL_HISTORY key=ID_CALL item=CALL}
					<tr>						
						<td>{$CALL.phone}</td>
						<td>{$CALL.calldate}</td>
						<td>{$CALL.agent}</td>
						<td>{$CALL.status}</td>
                        <td>{if isset($CALL.note)}<a href="javascript:void(0)" onclick="view_note('{$CALL.id}')">Xem</a>{/if}</td>
                        <td>{if isset($CALL.delivery)}<a href="javascript:void(0)" onclick="view_delivery('{$CALL.delivery_id}')">Xem</a>{/if}</td>
					</tr>
					{/foreach}
					</table>
                    {*
					<div class="pagination" id="ticket_list">
						<ul>
							<li><a href="#ticket_list">&laquo;</a></li>
							<li class="active"><a href="#ticket_list" class="page_select" onclick="select_page_ticket(this)">1</a></li>
							<li><a href="#ticket_list" onclick="select_page_ticket(this)">2</a></li>
							<li><a href="#ticket_list" onclick="select_page_ticket(this)">3</a></li>
							<li><a href="#ticket_list" onclick="select_page_ticket(this)">4</a></li>
							<li><a href="#ticket_list">&raquo;</a></li>
						</ul>
					</div>*}
				</div>
				<div id="mca-panel" class="panel"></div>
				<div id="airtime-panel" class="panel"></div>
			</div>
		</div>			
	</div>
	
	<!-- hidden items, use for "load more items" -->
	<div id="hidden-items" style="display:none">
	{foreach from=$AGENT_STATUS key=AGENT_KEY item=AGENT name=item}			
		{if $smarty.foreach.item.index >= $ITEM_LIMIT}
			<div id={$AGENT_KEY} class="{if $AGENT.agentstatus eq 'offline'}item invisible{else}item{/if}" data-status="{$AGENT.agentstatus}">
				<div class="item-info">
					<div class="item-left">
						<img id="{$AGENT_KEY}-image" class="status" src="{$AGENT.img_status}" title="{$AGENT.img_status_title}"/>												
						<a href="javascript:void(0)"><img id="{$AGENT_KEY}-logout_btn" src="{$THEME_PATH}/images/logout-ic.png" title="Kết thúc phiên làm việc của tổng đài viên" onclick="do_logout('{$AGENT.agent_number}')" {if $AGENT.agentstatus eq 'offline'}style="display:none;"{/if}/></a>												
						<a class="note" href="javascript:void(0)"><img id="{$AGENT_KEY}-addnote_btn" src="{$THEME_PATH}/images/note-ic.png" title="Ghi chú cuộc gọi này" onclick="get_addnote('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}')" {if $AGENT.agentstatus ne 'oncall'}style="display:none;"{/if}/></a>																												
						<table class="detail">
							<tr>
								<th>Agent:</th>
								<td>{$AGENT.agentname}</td>
							</tr>
							<tr>
								<th><span id="{$AGENT_KEY}-statuslabel">{$AGENT.status_time_label}</span></th>
								<td><span id="{$AGENT_KEY}-sec_laststatus">{$AGENT.status_time}</span></td>
							</tr>
							<tr>
								<th>Khách hàng:</th>
								<td><span id="{$AGENT_KEY}-customer">{if $AGENT.agentstatus eq 'oncall'}{$AGENT.customer}{else}(not on call){/if}</span></td>
							</tr>
						</table>
					</div>
					<div class="item-right">
						<h4>Tổng call</h4>
						<h1 class="total-call"><span id="{$AGENT_KEY}-num_calls">{$AGENT.num_calls}</span></h1>
					</div>
				</div>
				<div class="item-buttons">
					<a id="{$AGENT_KEY}-spycall_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-spycall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping-ic.png {else}{$THEME_PATH}/images/wiretapping_disable-ic.png{/if}" title="Nghe xen không tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}',false)"/></a>
					<a id="{$AGENT_KEY}-spycall_whisper_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-spycall_whisper_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping_2-ic.png {else}{$THEME_PATH}/images/wiretapping_2_disable-ic.png{/if}" title="Nghe xen có tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}',true)"/></a>
					<a id="{$AGENT_KEY}-hangoff_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-hangoff_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/hang_off-ic.png {else}{$THEME_PATH}/images/hang_off_disable-ic.png{/if}" title="Ngắt cuộc gọi này" onclick="do_hangup('{$AGENT.agent_number}')"/></a>					
					<a id="{$AGENT_KEY}-pickcall_link" href="javascript:void(0)"><img id="{$AGENT_KEY}-pickcall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/change-ic.png {else}{$THEME_PATH}/images/change_disable-ic.png{/if}" title="Lấy cuộc gọi này" onclick="do_transfer('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}')"/></a>										
					<p class="timer" title="Tổng thời gian nghe máy"><span id="{$AGENT_KEY}-sec_calls">{$AGENT.sec_calls_time}</span></p>
				</div>
			</div><!-- end item -->	
		{/if}
	{/foreach}				
	</div><!-- end hidden-items -->
</div><!-- end container -->
<div id="note-dialog" title="Note">
	<form id="note-form" action="">
		<div class="row">
			<textarea id="note-content" rows="6" cols="53" placeholder="Note content..."></textarea>
		</div>
		<div class="row">
			<div class="buttons">
				<input type="submit" id="btnSubmit" class="btn" value="Lưu"/>
				<input type="button" id="btnClose" class="btn" value="Hủy" />
			</div>
		</div>
	</form>
</div>
<!-- dialog VIEW -->
<div id="dialog-view" title="Xem ghi chú" style="font-size:12px;">
    <div class="row">
        <textarea id="dialog-view-content" style="width:440px;height:168px;" readonly></textarea>
    </div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose-dialog-view" value="Thoát" />
        </div>
    </div>
</div>
<!-- dialog "Save" -->
<!-- dialog VIEW -->
<div id="dialog-delivery" title="Xem yêu cầu giao vé" style="font-size:12px;">
    <div class="row">
        <label for="view-delivery-customer_name">Tên khách hàng: </label>
        <input type="text" id="view-delivery-customer_name" readonly>
        <span> 	&nbsp;</span>
        <label for="view-delivery-customer_phone">Số điện thoại: </label>
        <input type="text" id="view-delivery-customer_phone">
    </div>
    <div class="row">
        <label >Ngày mua vé: </label>
        <input type="text" id="view-delivery-purchase_date" readonly>
        <span> 	&nbsp;</span>
        <label >Booker: </label>
        <input type="text" id="view-delivery-agent_name" readonly>
    </div>
    <div class="row">
        <label >Địa chỉ: </label>
        <input type="text" id="view-delivery-deliver_address" style="width:411px;" readonly>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <label >Giá vé: </label>
        <input type="text" id="view-delivery-price" readonly>
        <span> 	&nbsp;</span>
        <label >Thuế: </label>
        <input type="text" id="view-delivery-tax" readonly>
    </div>
    <div class="row">
        <label >Chiết khấu: </label>
        <input type="text" id="view-delivery-discount" readonly>
        <span> 	&nbsp;</span>
        <label >Tỉ giá: </label>
        <input type="text" id="view-delivery-rate" readonly>
    </div>
    <div class="row">
        <label >Tổng cộng: </label>
        <input type="text" id="view-delivery-pay_amount" readonly>
    </div>
    <div class="row">
        <label >Tình trạng: </label>
        <input type="text" id="view-delivery-status" readonly>
        <span> 	&nbsp;</span>
        <label >Mã vé: </label>
        <textarea id="view-delivery-ticket_code" style="width:133px;height:60px;" readonly></textarea>
    </div>
    <div class="row">
        <label >Người giao vé: </label>
        <input type="text" id="view-delivery-delivery_name" readonly>
        <span> 	&nbsp;</span>
        <label >Ngày phân công: </label>
        <input type="text" id="view-delivery-delivery_date" readonly>
    </div>
    <div class="row">
        <label >Chi nhánh: </label>
        <input type="text" id="view-delivery-office" readonly>
        <span> 	&nbsp;</span>
        <label >Ngày nhận tiền: </label>
        <input type="text" id="view-delivery-collection_date" readonly>
    </div>
    <div class="row">&nbsp;</div>
    <div class="row">
        <div class="buttons">
            <input type="button" class="btn" id="btnClose-dialog-delivery" value="Thoát" />
        </div>
    </div>
</div>