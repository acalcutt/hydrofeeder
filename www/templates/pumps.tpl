{include file="header.tpl" title="Pumps" onLoad="NewPumpSched()"}

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
</table>
<br/>
<form action="pumps.php?func=insertpumpjob" method="post" enctype="multipart/form-data">
<table align="center">
	<tr>
		<th>Manual Pump</th>
	</tr>
	<tr>
		<td>
			<div align=left>
				<button type="button" onclick="NewPumpSched()">Add Row</button>
				</br>
			</div>
			<table id="PumpSchedTable" border="1" cellspacing="0">
			  <tr>
				<th style="width: 150px">Pump</td>
				<th style="width: 150px">Add ml</td>
				<th style="width: 100px">Options</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			</br>
			<button type="submit">Submit Manual Pump Job</button>
		</td>
	</tr>
</table>
</form>

<script>
function NewPumpSched() {
	
    var table = document.getElementById("PumpSchedTable");
    var row = table.insertRow(-1);
    var cell1 = row.insertCell(0);
	var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);
	var pumps = {$pumps|json_encode};
	var selectList = document.createElement("select");
	selectList.setAttribute("id", "mySelect");
	selectList.setAttribute("name", "pumpsched[id][]");
	for(var i = 0;i<pumps.length;i++){
	
		var option = document.createElement("option");
		option.setAttribute("value", pumps[i].id);
		option.text = pumps[i].name;
		selectList.appendChild(option);
	}	
	cell1.appendChild(selectList);
	cell2.innerHTML = '<input name="pumpsched[addml][]" value=0>'
    cell3.innerHTML = '<button type="button" onclick="DeleteTableRow(this)">Delete</button>';
}
function DeleteTableRow(o) {
	var p=o.parentNode.parentNode;
	p.parentNode.removeChild(p);
}
</script>

{include file="footer.tpl"}