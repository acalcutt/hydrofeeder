{include file="header.tpl" title=switches}

<table align="center">
	<tr>
		<td>
			[<a class="links" href="switches.php?func=add" title="New Switch">New Switch</a>][<a class="links" href="switches.php?func=actions" title="Switch Actions">Switch Actions</a>]
		</td>
	</tr>
	<tr>
		<th>Switches</th>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<th style="width: 150px">Name</th>
					<th style="width: 25px">GPIO</th>
					<th style="width: 50px">ON</th>
					<th style="width: 100px">Options</th>
				</tr>
				{foreach item=switch from=$switches}
				<tr>
					<td>{$switch.name}</td>
					<td>{$switch.gpio}</td>
					<td>{$switch.on_value}</td>
					<td><a class="links" href="switches.php?func=edit&id={$switch.id}" title="[Edit]">[Edit]</a><a class="links" href="switches.php?func=delete&id={$switch.id}" title="[Delete]">[Delete]</a></td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="4">There are no switches set up</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>

{include file="footer.tpl"}