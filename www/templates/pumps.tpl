{include file="header.tpl" title=Pumps}

<table align="center">
	<tr>
		<td>
			[<a class="links" href="pumps.php?func=add" title="New Grow">New Pump</a>]
		</td>
	</tr>
	<tr>
		<th>Pumps</th>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<th style="width: 150px">Name</th>
					<th style="width: 25px">GPIO</th>
					<th style="width: 150px">ml PER_MINUTE</th>
					<th style="width: 50px">ON</th>
					<th style="width: 50px">OFF</th>
					<th style="width: 100px">Options</th>
				</tr>
				{foreach item=pump from=$pumps}
				<tr>
					<td>{$pump.name}</td>
					<td>{$pump.gpio}</td>
					<td>{$pump.ml_per_minute}</td>
					<td>{$pump.on_value}</td>
					<td>{$pump.off_value}</td>
					<td><a class="links" href="pumps.php?func=edit&id={$pump.id}" title="[Edit]">[Edit]</a><a class="links" href="pumps.php?func=delete&id={$pump.id}" title="[Delete]">[Delete]</a></td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="6">There are no pumps set up</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
	<tr>
		<th>Manual Pump</th>
	</tr>
	<tr>
		<td>
		
		</td>
	</tr>
</table>

{include file="footer.tpl"}