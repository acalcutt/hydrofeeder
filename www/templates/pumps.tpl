{include file="header.tpl" title=Pumps}

<table align="center" width="569" border="1" cellpadding="4" cellspacing="0">
	<tbody>
		<tr class="style4">
			<th colspan="6">Pumps</th>
		</tr>
		<tr class="style4">
			<th>ID</th>
			<th>Name</th>
			<th>GPIO</th>
			<th>ml PER_MINUTE</th>
			<th>ON VALUE</th>
			<th>OFF VALUE</th>
		</tr>
		{foreach item=pump from=$pumps}
		<tr>
			<td>{$pump.id}</td>
			<td>{$pump.name}</td>
			<td>{$pump.gpio}</td>
			<td>{$pump.ml_per_minute}</td>
			<td>{$pump.on_value}</td>
			<td>{$pump.off_value}</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="6">There are no pumps set up</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="footer.tpl"}