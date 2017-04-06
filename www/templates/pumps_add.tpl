{if $func eq 'edit'}
{include file="header.tpl" title="Edit Pump"}
<form action="pumps.php?func=update" method="post" enctype="multipart/form-data">
{else}
{include file="header.tpl" title="Add Pump"}
<form action="pumps.php?func=insert" method="post" enctype="multipart/form-data">
{/if}

<input name="id" type="hidden" value="{$pumpinfo.id}">

<table align="center">
	<tr>
		<td>
			<a class="links" href="pumps.php" title="New Grow">Back to Pump List</a>
		</td>
	</tr>
	<tr>
		<th>Pump Information</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td>Name</td>
					<td>
						<input name="name" type="text" value="{$pumpinfo.name}">
					</td>
				</tr>
				<tr>
					<td>GPIO</td>
					<td>
						<input name="gpio" type="text" value="{$pumpinfo.gpio}">
					</td>
				</tr>
				<tr>
					<td>ml Per Minute</td>
					<td>
						<input name="ml_per_minute" type="text" value="{$pumpinfo.ml_per_minute}">
					</td>
				</tr>
				<tr>
					<td>On Value</td>
					<td>
						<input name="on_value" type="text" value="{$pumpinfo.on_value}">
					</td>
				</tr>
				<tr>
					<td>Off Value</td>
					<td>
						<input name="off_value" type="text" value="{$pumpinfo.off_value}">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			</br>
			<button type="submit">Save</button>
		</td>
	</tr>
</table>
</form>

{include file="footer.tpl"}