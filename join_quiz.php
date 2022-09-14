<?php include ('db_connect.php');
$status = 0;
if(isset($_GET['quiz'])){
	$qry = $conn->query("SELECT * FROM quiz_list where md5(id) = '".$_GET['quiz']."' ")->fetch_array();
	foreach($qry as $k=> $v){
		$$k = $v;
	}
if($status == 3){
	echo "<script>location.replace('index.php?page=my_quiz_result&quiz=".md5($id)."')</script>";
}

$question = $conn->query("SELECT * FROM questions where qid = $id");
	if($question->num_rows > 0){
		while($qrow = $question->fetch_assoc()):
			$quest[$qrow['id']] = $qrow['question'];
			$qpoints[$qrow['id']] =$qrow['points'];
		$qry2 = $conn->query("SELECT * FROM question_opt where question_id = '".$qrow['id']."' ");
		while($row = $qry2->fetch_assoc()){
			$opt[$qrow['id']][] = $row;
		}
		endwhile;
		}
$question = $conn->query("SELECT * FROM questions where qid = $id and status = 0 order by id asc limit 1")->fetch_array()['id'];
	}
 ?>
 <style type="text/css">
	#qfield {
		display: grid;
		min-height: 30vh;
		justify-content: center;
		align-items: center;
		width: calc(100%)
	}
	#opt-field .card{
		cursor: pointer;

	}
	#opt-field .card:hover,#opt-field .card.active{
    background: #00c4ffcc;
    color: white;
    font-weight: 600;

	}

</style>
<div class="container-fluid">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body" id="field">
					<?php if($status < 2): ?>
				<div id="qfield">
							<h4><i>Waiting for teacher to start the quiz...</i></h4>
								
						
				</div>
				
						<?php endif; ?>
				
			</div>
		</div>
	</div>
</div>
<div style="display: none" id="clone_q">
	<div class="col-md-12 q">
					<p>Question: <b id="qtxt"></b></p>
					<div class="row" id="opt-field">
						
					</div>
				</div>
</div>
<script>
	start_load()
	$(document).ready(function(){
		if('<?php echo !isset($_GET['quiz']) ?>' == 1){
			end_load()
			uni_modal("Join Quiz",'find_quiz.php')
		}else{
			$.ajax({
			url:"ajax.php?action=save_participant",
			method:"POST",
			data:{code:"<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>"},
			success:function(resp){
				if(resp == 1){
					websocket.onopen = function(event) { 
						websocket.send(JSON.stringify({'type':'q_participate',"id":"<?php echo $_SESSION['login_id'] ?>","name":"<?php echo ucwords($_SESSION['login_name']) ?>","code":"<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>"}));
						if('<?php echo isset($status) ? $status : 0 ?>' == 2){
							load_question('<?php echo isset($question) ? $question:'' ?>')
						}
						end_load()
					}
					
				}
			}
		})
		}
		websocket.onmessage = function(event) {
				var Data = JSON.parse(event.data);
				if(Data == null)
					return false;
				if(Data.type=="request_quiz_connected" && Data.qid == '<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>'){
					websocket.send(JSON.stringify({'type':'q_participate',"id":"<?php echo $_SESSION['login_id'] ?>","name":"<?php echo ucwords($_SESSION['login_name']) ?>","code":"<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>"}));
				}
				if(Data.type=="start_quiz" && Data.qid == '<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>'){
					load_question(Data.start)
				}
				if(Data.type=="finish_quiz" && Data.qid == '<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>'){
					location.replace("index.php?page=my_quiz_result&quiz=<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>")
				}

		};
	})
	function load_question($qid){
	var question = <?php echo isset($_GET['quiz']) ? json_encode($quest) : '{}' ?>;
	var opt = <?php echo isset($_GET['quiz']) ? json_encode($opt) : '{}' ?>;
	var field = $('#clone_q .q').clone()
	field.find('#qtxt').html(question[$qid])
	var opt_html = '';
	Object.keys(opt[$qid]).map(k=>{
		opt_html +="<div class='col-md-6 mb-2 opt-item' data-id='"+opt[$qid][k].id+"'><div class='card'><div class='card-body'>"+opt[$qid][k].option_txt+"</div></div></div>"
	})
	field.find('#opt-field').html(opt_html)
		$('#field').html(field)
		load_answered()
	$('#field').find('.opt-item').click(function(){
		var id = $(this).attr('data-id')
		var qid = '<?php echo isset($id) ? $id :'' ?>'
		$('.opt-item .card').removeClass("active")
		$(this).find('.card').addClass("active")
		start_load()
		$.ajax({
			url:'ajax.php?action=save_answer',
			method:'POST',
			data:{option_id:id,quiz_id:qid},
			success:function(resp){
				if(resp == 1){
					websocket.send(JSON.stringify({'type':'answer_question',"code":"<?php echo isset($_GET['quiz']) ? $_GET['quiz'] :'' ?>"}));
					end_load()
				}
			}
		})
	})
}
function load_answered(){
	$.ajax({
		url:'ajax.php?action=load_my_answer',
		method:'POST',
		data:{id:'<?php echo isset($id) ?$id : '' ?>'},
		success:function(resp){
			resp=JSON.parse(resp)
			if(Object.keys(resp).length > 0){
				$('.opt-item .card').removeClass("active")
				Object.keys(resp).map(k=>{
					$('.opt-item[data-id="'+k+'"]').find('.card').addClass("active")
				})
				end_load()
			}
		}
	})
}
</script>