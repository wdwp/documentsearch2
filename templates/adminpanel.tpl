{if isset($error)}
	<h3><font color="red">{$error}</font></h3>
{else}
	{if isset($message)}
    	<h3>{$message}</h3>
	{/if}
{/if}

{literal}
<script type="text/javascript">
//<![CDATA[
function selectAll() {
  checkboxes = document.getElementsByTagName("input");
  for (i=0; i<checkboxes.length ; i++)
    {
      if (checkboxes[i].type == "checkbox") checkboxes[i].checked=!checkboxes[i].checked;
    }
}
$(function() {
	$("#category").prepend("<option value=''>{/literal}{$filteralltext}{literal}</option>").val('');
	$("#category").change(function () {
		sx = $("input[name=_sx_]").val();
		s='/admin/moduleinterface.php?module=DocumentSearch&_sx_='+sx;
		cat= $("#category option:selected").val();
		d='{/literal}{$actionid}{literal}category='+cat;
 		s = s+' #records';
		$("#records").load(s, d, function(){
		});
	});
});
//]]>
</script>
{/literal}
{$tabs_start}
	{$start_general_tab}
		{$welcome_text}
		<p class="pageinput">{$filtertext} {$category}</p>
		{$formstart}
		<div id="records">
			{if isset($module_message)}<h2>{$module_message|escape}</h2>{/if}
			<h3>{$title_num_records}</h3>
		{if $add != ''}<div>{$add}</div>{/if}
     		<table cellspacing="0" class="pagetable">
				<thead>
       				<tr>
         				<th>{$idtext}</th>
         				<th>{$nametext}</th>
         				<th>{$filetext}</th>
						<th>{$categorytext}</th>
         				<th class="pageicon">&nbsp;</th>
						<th class="pageicon">&nbsp;</th>
						<!--<th class="pageicon"><a href="javascript:selectAll();">{$selecttext}</a></th>-->
       				</tr>
				</thead>
				<tbody>
				{foreach from=$records item=entry}
       				<tr class="{$entry->rowclass}">
         				<td>{$entry->id}</td>
         				<td>{$entry->name}</td>
         				<td>{$entry->location}</td>
						<td>{$entry->category}</td>
						<td>{$entry->editlink}</td>
						<td>{$entry->deletelink}</td>
						<!--<td>{$entry->select}</td>-->
       				</tr>
				{/foreach}
				</tbody>
     		</table>
		</div>
		{if $add != ''}<div>{$add}</div>{/if}
		{if $recordcount > 0}
  			<div class="pageoptions" style="float: right;">
    				{if isset($submit_massdelete)}{$submit_massdelete}{/if}
  			</div>
			<div style="clear:both"></div>
		{/if}
		{$formend}
	{$tab_end}
{$tabs_end}