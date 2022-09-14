<?php include('db_connect.php') ?>
<?php 
	$qry = $conn->query("SELECT q.*,u.name as tname FROM quiz_list q inner join users u on u.id =q.user_id where q.id = ".$_GET['id'])->fetch_array();
	foreach($qry as $k =>$v){
		$$k=$v;
	}
	$qquery = $conn->query("SELECT * FROM questions where qid = ".$_GET['id']." order by order_by asc");

		while($row=$qquery->fetch_array()){
			$q_points[$row['id']] = $row['points'];
		}
		$answers = $conn->query("SELECT * FROM answers where quiz_id = ".$_GET['id']);
		while($row=$answers->fetch_array()){
			if(!isset($ppoints[$row['user_id']]))
			$ppoints[$row['user_id']] = 0;
		if(!isset($right[$row['user_id']]))
			$right[$row['user_id']] = 0;
			$ppoints[$row['user_id']] += ($q_points[$row['question_id']] * $row['is_right']);
			if($row['is_right'] == 1)
				$right[$row['user_id']] += 1;

		}
?>
<style>
	table{
		width:100%;
		border-collapse:collapse;
	}
	table,thead,tbody,tr,td,th{
		border:1px solid black;
	}
	.text-center{
		text-align: center
	}
</style>
<p class="text-center"><b>Quiz Participants Records</b></p>
<h3 class="text-center"><b><?php echo $title ?></b></h3>

<hr>
<center><small>Created By: <?php echo ucwords($tname) ?></small></center>
<table>
	<colgroup>
		<col width="10%">
		<col width="40%">
		<col width="25%">
		<col width="25%">
	</colgroup>
	<thead>
		<tr>
			<th>#</th>
			<th>Participant</th>
			<th>Correct Item/s</th>
			<th>Score</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 1;
			$participants = $conn->query("SELECT distinct(a.user_id),u.name as uname FROM answers a inner join users u on  u.id = a.user_id where a.quiz_id = ".$qry['id']);
			while($row=$participants->fetch_assoc()):
		?>
		<tr>
			<td><center><?php echo $i++ ?></center></td>
			<td><?php echo ucwords($row['uname']) ?></td>
			<td><center><?php echo $right[$row['user_id']] ?></center></td>
			<td><center><?php echo $ppoints[$row['user_id']] ?></center></td>

		</tr>
	<?php endwhile; ?>
	</tbody>
</table>
