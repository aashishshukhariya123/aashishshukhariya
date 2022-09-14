<?php include('db_connect.php') ?>
<?php 
	$qry = $conn->query("SELECT * FROM quiz_list where id = ".$_GET['id'])->fetch_array();
	$status = array("pending","waiting","on-going","done");
	?>
	<div class="container-fluid">
		<div class="col-md-12 alert alert-primary">	
			<p>Title: <?php echo $qry['title'] ?></p>
			<p>Code: <?php echo md5($qry['id']) ?></p>
			<p>Status: <?php echo ucwords($status[$qry['status']])  ?></p>
			
		</div>
		<?php if($qry['status'] == 0 ): ?>
		<button class="btn btn-primary bt-sm" id="new_question"><i class="fa fa-plus"></i>	Add Question</button>
		<?php endif; ?>
		<?php if($qry['status'] < 2): ?>
		<a class="btn btn-primary bt-sm" id="start_quiz" href="index.php?page=quiz_board&quiz=<?php echo md5($qry['id']) ?>"><i class="fa fa-play"></i> Start Quiz</a>

		<?php elseif($qry['status'] == 2): ?>
		<a class="btn btn-primary bt-sm" id="start_quiz" href="index.php?page=quiz_board&quiz=<?php echo md5($qry['id']) ?>"><i class="fa fa-pause"></i> Continue Quiz</a>

		<?php endif; ?>
		<br>
		<br>
		<div class="row">
		<div class="<?php echo $qry['status'] == 3 ? 'col-md-8' : 'col-md-12' ?>">
		<div class="card mr-4">
			<div class="card-header">
				Questions
			</div>
			<div class="card-body">
				<div class="container-fluid">
				<?php
					$i = 1;

					$qquery = $conn->query("SELECT * FROM questions where qid = ".$_GET['id']." order by order_by asc");
					if($qquery->num_rows > 0){
						$qry2 = $conn->query("SELECT * FROM question_opt where question_id in (SELECT id FROM questions where qid = ".$_GET['id']." ) ");
						while($row = $qry2->fetch_assoc()){
							$opt[$row['question_id']][] = $row;
							$ans[$row['id']] = 0;
						}
						while($row = $qry2->fetch_assoc()){
							$opt[$row['question_id']][] = $row;
						}
						while($row=$qquery->fetch_array()){
							$q_points[$row['id']] = $row['points'];
						}
						$answers = $conn->query("SELECT * FROM answers where quiz_id = ".$_GET['id']);
						while($row=$answers->fetch_array()){
							$ans[$row['option_id']] += 1;
							if(!isset($ppoints[$row['user_id']]))
							$ppoints[$row['user_id']] = 0;
							$ppoints[$row['user_id']] += ($q_points[$row['question_id']] * $row['is_right']);
						}
					}
					$qquery = $conn->query("SELECT * FROM questions where qid = ".$_GET['id']." order by order_by asc");
					while($row=$qquery->fetch_array()){
						?>
						<div class="card mb-3">
						<div class="card-body">
							Question <?php echo $i++ ?> :<?php echo $row['question'] ?><br>
							<hr>
							<p><small><i><b>Points:<?php echo $row['points'] ?></b></i></small></p>
							<p><b>Options:</b></p>
							<div class="row">
							<?php foreach($opt[$row['id']] as $orow): ?>
							<?php if($qry['status'] == 0): ?>

							<p class="col-md-6">
								<?php if($orow['is_right'] == 1): ?> 
									<span class="text-success"> <i class="fa fa-check"></i> </span>
								<?php else: ?> 
									<span class="text-danger"> <i class="fa fa-times"></i> </span>
								<?php endif; ?> 
								<b><?php echo $orow['option_txt'] ?></b>
							</p>
						<?php else: ?>
							<div class="col-md-6 mb-2">
								<div class="card <?php echo $orow['is_right'] ==1 ? "bg-success" : "bg-danger" ?>">
									<div class="card-body text-white">
										<p><b><?php echo $orow['option_txt'] ?></b></p>
										<p class="badge badge-primary"><i><?php echo $ans[$orow['id']].' participants selected this.' ?></i></p>
									</div>
								</div>
							</div>
						<?php endif; ?>

							<?php endforeach; ?>
							</div>
							<?php if($qry['status'] == 0): ?>
							<hr>
							<center>
								 <button class="btn btn-sm btn-outline-primary edit_question" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-edit"></i> Edit</button>
								<button class="btn btn-sm btn-outline-danger delete_question" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-trash"></i> Delete</button>
							</center>
						<?php endif; ?>
						</div>
						</div>
				<?php
					}
				?>
				</div>
		</div>
	</div>
</div>
<?php if($qry['status'] == 3): ?>
	<div class="col-md-4">
		<div class="card card-primary">
			<div class="card-header">
				Participants
				<span class="float-right"><button class="btn btn-sm btn-success" id="print_records" type="button"><i class="fa fa-print"></i> Print Records</button></span>
			</div>
			<div class="card-body">
				<ul class="list-group">
					<?php
						$participants = $conn->query("SELECT distinct(a.user_id),u.name as uname FROM answers a inner join users u on  u.id = a.user_id where a.quiz_id = ".$qry['id']);
						while($row=$participants->fetch_assoc()):

					?>
					<li class="list-group-item">
						<div class="row">
							<div class="col-md-10">
							<p><?php echo ucwords($row['uname']) ?></p>
							<p class="bage-primary"><i>Score: <?php echo $ppoints[$row['user_id']] ?></i></p>
							</div>
							<div class="col-md-2">
								<button class="btn btn-outline-info btn-sm view_result" data-id="<?php echo $row['user_id'] ?>" type="button" data-name="<?php echo ucwords($row['uname']) ?>" ><i class="fa fa-eye"></i></button>
							</div>
						</div>
							
						</li>
					<?php endwhile; ?>
				</ul>
			</div>
		</div>
	</div>
<?php endif; ?>
</div>
</div>
	<script>
		$('#new_question').click(function(){
			uni_modal("New Question","manage_question.php?qid=<?php echo $_GET['id'] ?>","mid-large")
		})
		$('.edit_question').click(function(){
			uni_modal("New Question","manage_question.php?qid=<?php echo $_GET['id'] ?>&id="+$(this).attr('data-id'),"mid-large")
		})
		$('.view_result').click(function(){
			uni_modal("Quiz Result of "+$(this).attr('data-name'),"view_result.php?qid=<?php echo $_GET['id'] ?>&uid="+$(this).attr('data-id'),"large")
		})
		$('#print_records').click(function(){
			var nw = window.open("print_quiz_records.php?id=<?php echo $qry['id'] ?>","_blank","height=600,width=800")
			nw.print()
			setTimeout(function(){
				nw.close()
			},700)
		})
		$('.delete_question').click(function(){
		_conf("Are you sure to delete this question?","delete_question",[$(this).attr('data-id')])
	})
	
	function delete_question($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_question',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	</script>