{include file="header.tpl" title=Grows}

<table align="center" width="569" border="1" cellpadding="4" cellspacing="0">
	<tbody>
		<tr class="style4">
			<th colspan="6">Grows</th>
		</tr>
		<tr class="style4">
			<th>ID</th>
			<th>Enabled</th>
			<th>Name</th>
			<th>Start Date</th>
		</tr>
		{foreach item=grow from=$grows}
		<tr>
			<td>{$pump.id}</td>
			<td>{$pump.enabled}</td>
			<td>{$pump.name}</td>
			<td>{$pump.startdate}</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="6">There are no grow set up :-(</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="footer.tpl"}