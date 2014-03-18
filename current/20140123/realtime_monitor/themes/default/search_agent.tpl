		{foreach from=$AGENT_STATUS key=AGENT_KEY item=AGENT name=item}			
			{if $smarty.foreach.item.index < $ITEM_LIMIT}
			<div id={$AGENT_KEY} class="{if $AGENT.agentstatus eq 'offline'}item invisible{else}item{/if}" data-status="{$AGENT.agentstatus}">
				<div class="item-info">
					<div class="item-left">
						<img id="{$AGENT_KEY}-image" class="status" src="{$AGENT.img_status}" title="{$AGENT.img_status_title}"/>												
						<a href="#"><img id="{$AGENT_KEY}-logout_btn" src="{if $AGENT.agentstatus ne 'offline'}{$THEME_PATH}/images/logout-ic.png{/if}" title="{if $AGENT.agentstatus ne 'offline'}Kết thúc phiên làm việc của tổng đài viên{/if}" onclick="do_logout('{$AGENT.agent_number}')"/></a>												
						<a class="note" href="#"><img id="{$AGENT_KEY}-addnote_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/note-ic.png{/if}" title="{if $AGENT.agentstatus eq 'oncall'}Ghi chú cuộc gọi này{/if}" onclick="get_addnote('{$AGENT.agent_number}','{$SUPERVISOR_NUMBER}')"/></a>																												
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
					<a id="{$AGENT_KEY}-spycall_link" href="#"><img id="{$AGENT_KEY}-spycall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping-ic.png {else}{$THEME_PATH}/images/wiretapping_disable-ic.png{/if}" title="Nghe xen không tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}',false)"/></a>
					<a id="{$AGENT_KEY}-spycall_whisper_link" href="#"><img id="{$AGENT_KEY}-spycall_whisper_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/wiretapping_2-ic.png {else}{$THEME_PATH}/images/wiretapping_2_disable-ic.png{/if}" title="Nghe xen có tư vấn" onclick="do_spycall('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}',true)"/></a>
					<a id="{$AGENT_KEY}-hangoff_link" href="#"><img id="{$AGENT_KEY}-hangoff_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/hang_off-ic.png {else}{$THEME_PATH}/images/hang_off_disable-ic.png{/if}" title="Ngắt cuộc gọi này" onclick="do_hangup('{$AGENT.agent_number}')"/></a>					
					<a id="{$AGENT_KEY}-pickcall_link" href="#"><img id="{$AGENT_KEY}-pickcall_btn" src="{if $AGENT.agentstatus eq 'oncall'}{$THEME_PATH}/images/change-ic.png {else}{$THEME_PATH}/images/change_disable-ic.png{/if}" title="Lấy cuộc gọi này" onclick="do_transfer('{$AGENT.agent_number}','{$SUPERVISOR_QUEUE}')"/></a>										
					<p class="timer" title="Tổng thời gian nghe máy"><span id="{$AGENT_KEY}-sec_calls">{$AGENT.sec_calls_time}</span></p>
				</div>
			</div><!-- end item -->				
			{/if}
		{/foreach}				