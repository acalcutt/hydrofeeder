{include file="header.tpl" title="Grows" onLoad="LoadDynamicItems()"}
{if $func eq 'edit'}
<form action="grows.php?func=update" method="post" enctype="multipart/form-data">
{else}
<form action="grows.php?func=insert" method="post" enctype="multipart/form-data">
{/if}

<input name="id" type="hidden" value="{$growinfo.id}">
<input name="created" type="hidden" value="{$growinfo.created}">
<input name="hidden" type="hidden" value="{$growinfo.hidden}">

<table align="center">
	<tr>
		<td>
			<a class="links" href="grows.php" title="New Grow">Back to Grow List</a>
		</td>
	</tr>
	<tr>
		<th>Grow Information</td>
	</tr>
	<tr>
		<td>
			<table border="1" cellspacing="0">
				<tr>
					<td>Name</td>
					<td>
						<input name="name" type="text" value="{$growinfo.name}">
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>
						<input name="description" type="text" value="{$growinfo.description}">
					</td>
				</tr>
				<tr>
					<td>Enabled</td>
					<td>
						<input name="enabled" type="checkbox" value="1" {if $growinfo.enabled == 1}checked="checked"{/if}>
					</td>
				</tr>
				<tr>
					<td>Created</td>
					<td>
						<input name="created_local" type="text" value="{$growinfo.created_local}" disabled>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<th>Pump Schedule</td>
	</tr>
	<tr>
		<td>
			<div align=left>
				<button type="button" onclick="NewPumpSched()">Add Pump Schedule Row</button>
				</br>
			</div>
			<table id="PumpSchedTable" border="1" cellspacing="0">
			  <tr>
				<th style="width: 150px">Pump</td>
				<th style="width: 150px">Start Date</td>
				<th style="width: 150px">End Date</td>
				<th style="width: 150px">Add ml</td>
				<th style="width: 75px">Enabled</td>
				<th style="width: 100px">Options</td>
			  </tr>
			</table>
		</td>
	</tr>
	<tr>
		<td></br></td>
	</tr>
	<tr>
		<th>Notes</th>
	</tr>
	<tr>
		<td>
			<div align=left>
				<button type="button" onclick="NewNote()">Add Note Row</button>
				</br>
			</div>
			<table id="NotesTable" border="1" cellspacing="0">
			  <tr>
				<th style="width: 150px">Note</td>
				<th style="width: 150px">Added</td>
				<th style="width: 100px">Options</td>
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

<script>
function LoadDynamicItems() {
	var table = document.getElementById("PumpSchedTable");
	var pumps = {$pumps|json_encode};
	var sched = {$SchedArray|json_encode};
	for(var j = 0;j<sched.length;j++){
		var sched_pump_id = sched[j].pump_id
		var sched_addml = sched[j].addml
		var sched_startdate = sched[j].startdate
		var sched_enddate = sched[j].enddate
		var sched_enabled = sched[j].enabled
		
		var row = table.insertRow(-1);
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);
		var cell3 = row.insertCell(2);
		var cell4 = row.insertCell(3);
		var cell5 = row.insertCell(4);
		var cell6 = row.insertCell(5);
		var selectList = document.createElement("select");
		selectList.setAttribute("id", "mySelect");
		selectList.setAttribute("name", "pumpsched[id][]");
		for(var i = 0;i<pumps.length;i++){
			var option = document.createElement("option");
			option.setAttribute("value", pumps[i].id);
			option.text = pumps[i].name;
			if (sched_pump_id == pumps[i].id) {
				option.setAttribute("selected", "");
			}			
			selectList.appendChild(option);
		}	
		cell1.appendChild(selectList);
		cell2.innerHTML = '<input type="date" name="pumpsched[startdate][]" value="' + sched_startdate + '">'
		cell3.innerHTML = '<input type="date" name="pumpsched[enddate][]" value="' + sched_enddate + '">'
		cell4.innerHTML = '<input name="pumpsched[addml][]" value="' + sched_addml + '">'
		if (sched_enabled = 1) {
			cell5.innerHTML = '<input name="pumpsched[enabled][]" type="checkbox" value="1" checked="checked">'
		}else{
			cell5.innerHTML = '<input name="pumpsched[enabled][]" type="checkbox" value="1" checked="unchecked">'
		}
		cell6.innerHTML = '<button type="button" onclick="DeleteTableRow(this)">Delete</button>';
	
	}	
	
	var table = document.getElementById("NotesTable");
	var notes = {$NoteArray|json_encode};
	for(var j = 0;j<notes.length;j++){
		var note = notes[j].note
		var note_date = new Date(notes[j].datestamp);
		
		var row = table.insertRow(-1);
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);
		var cell3 = row.insertCell(2);

	cell1.innerHTML = '<textarea rows="4" cols="50" name="notes[note][]">' + note + '</textarea>'
	cell2.innerHTML = '<input value="' + note_date.toLocaleString() + '" disabled><input type="hidden" name="notes[datestamp][]" value="' + note_date.toISOString() + '">'
	cell3.innerHTML = '<button type="button" onclick="DeleteTableRow(this)">Delete</button>';
	}

}

function NewNote() {

	var today = new Date();
    var table = document.getElementById("NotesTable");
    var row = table.insertRow(-1);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);

	cell1.innerHTML = '<textarea rows="4" cols="50" name="notes[note][]">'
	cell2.innerHTML = '<input value="' + today.toLocaleString() + '" disabled><input type="hidden" name="notes[datestamp][]" value="' + today.toISOString() + '">'
	cell3.innerHTML = '<button type="button" onclick="DeleteTableRow(this)">Delete</button>';
}

function NewPumpSched() {
	
	var today = (new Date()).toISOString().substring(0, 10);
    var table = document.getElementById("PumpSchedTable");
    var row = table.insertRow(-1);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
	var cell3 = row.insertCell(2);
	var cell4 = row.insertCell(3);
	var cell5 = row.insertCell(4);
	var cell6 = row.insertCell(5);
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
	cell2.innerHTML = '<input type="date" name="pumpsched[startdate][]" value="' + today + '">'
	cell3.innerHTML = '<input type="date" name="pumpsched[enddate][]" value="' + today + '">'
	cell4.innerHTML = '<input name="pumpsched[addml][]" value=0>'
	cell5.innerHTML = '<input name="pumpsched[enabled][]" type="checkbox" value="1" checked="checked">'
    cell6.innerHTML = '<button type="button" onclick="DeleteTableRow(this)">Delete</button>';
}
function DeleteTableRow(o) {
	var p=o.parentNode.parentNode;
	p.parentNode.removeChild(p);
}
</script>



{include file="footer.tpl"}