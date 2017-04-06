{include file="header.tpl" title=Grows}

<table align="center">
	<tr>
		<td>
			[<a class="links" href="grows.php?func=add" title="New Grow">New Grow</a>][{if $func eq 'hidden'}<a class="links" href="grows.php" title="Grows">Grows</a>{else}<a class="links" href="grows.php?func=hidden" title="Hidden Grows">Hidden Grows</a>{/if}]
		</td>
	</tr>
	<tr>
		{if $func eq 'hidden'}
		<th>Hidden Grows</th>
		{else}
		<th>Grows</th>
		{/if}
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
					<tr class="style4">
						<th style="width: 150px">Name</th>
						<th style="width: 150px">Description</th>
						<th style="width: 150px">Created</th>
						<th style="width: 75px">Enabled</th>
						<th style="width: 150px">Options</th>
					</tr>
					{foreach item=grow from=$grows}
					<tr>
						<td><a class="links" href="grows.php?func=edit&id={$grow.id}" title="{$grow.name}">{$grow.name}</a></td>
						<td>{$grow.description}</td>			
						<td>{$grow.created_local}</td>
						<td><input name="enabled" disabled type="checkbox" value="1" {if $grow.enabled == 1}checked="checked"{/if}></td>
						<td><a class="links" href="grows.php?func=edit&id={$grow.id}" title="[Edit]">[Edit]</a>{if $grow.hidden == 0}<a class="links" href="grows.php?func=hide&id={$grow.id}" title="[Hide]">[Hide]</a>{else}<a class="links" href="grows.php?func=unhide&id={$grow.id}" title="[Un-Hide]">[Un-Hide]</a>{/if}<a class="links" href="grows.php?func=delete&id={$grow.id}" title="[Delete]">[Delete]</a></td>
					</tr>
					{foreachelse}
					<tr>
						<td colspan="6">There are no grows to list :-(</td>
					</tr>
					{/foreach}
			</table>
		</td>
	</tr>
</table>




{include file="footer.tpl"}