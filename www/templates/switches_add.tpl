{if $func eq 'edit'}
{include file="header.tpl" title="Edit switch"}
<form action="switches.php?func=update" method="post" enctype="multipart/form-data">
{else}
{include file="header.tpl" title="Add switch"}
<form action="switches.php?func=insert" method="post" enctype="multipart/form-data">
{/if}

<input name="id" type="hidden" value="{$switchinfo.id}">

<table align="center">
	<tr>
		<td>
			<a class="links" href="switches.php" title="New Switch">Back to Switch List</a>
		</td>
	</tr>
	<tr>
		<th>switch Information</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td>Name</td>
					<td>
						<input name="name" type="text" value="{$switchinfo.name}">
					</td>
				</tr>
				<tr>
					<td>GPIO</td>
					<td>
						<input name="gpio" type="text" value="{$switchinfo.gpio}">
					</td>
				</tr>
				<tr>
					<td>On Value</td>
					<td>
						<input name="on_value" type="text" value="{$switchinfo.on_value}">
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