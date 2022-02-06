<?php

AssertForbidden("optimize");

if ($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Optimize tables", "optimize"));
makeBreadcrumbs($crumbs);

$rStats = Query("show table status");
while($stat = Fetch($rStats))
	$tables[$stat['Name']] = $stat;

$tablelist = "";
$total = 0;
foreach($tables as $table) {
	$cellClass = ($cellClass+1) % 2;
	$overhead = $table['Data_free'];
	$total += $overhead;
	$status = "OK";
	if ($overhead > 0) {
		Query("OPTIMIZE TABLE `{".$table['Name']."}`");
		$status = "<strong>Optimized</strong>";
	}

	$tablelist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\">{1}</td>
		<td>
			{2}
		</td>
		<td>
			{3}
		</td>
		<td>
			{4}
		</td>
	</tr>
",	$cellClass, $table['Name'], $table['Rows'], $overhead, $status);
}

write(
"
<table class=\"outline margin\">
	<tr class=\"header0\">
		<th colspan=\"7\">
			Table Status
		</th>
	</tr>
	<tr class=\"header1\">
		<th>
			Name
		</th>
		<th>
			Rows
		</th>
		<th>
			Overhead
		</th>
		<th>
			Final Status
		</th>
	</tr>
	{0}
	<tr class=\"header0\">
		<th colspan=\"7\" style=\"font-size: 130%;\">
			Excess trimmed: {1} bytes
		</th>
	</tr>
</table>

", $tablelist, $total);
