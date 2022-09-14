<?php 
include('db_connect.php');

$qry = $conn->query("SELECT * FROM quiz_list where md5(id) ='".$_GET['quiz']."'");
foreach($qry->fetch_array() as $k=> $v){
	$$k= $v;
}
if($status < 2){
$conn->query("UPDATE quiz_list set status = 1 where md5(id) ='".$_GET['quiz']."'");
$status = 1;
}
	$stat = array("pending","waiting...","on-going","done");

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
$pending = $conn->query("SELECT * FROM questions where qid = $id and status = 0 and id != '$question' ")->num_rows;
$is_last = $pending > 0 ? 0 :1;
?>

<style type="text/css">
	#qfield {
		display: grid;
		min-height: 30vh;
		justify-content: center;
		align-items: center;
		width: calc(100%)
	}
</style>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="mb-2 alert alert-primary">	
			<p>Title: <?php echo $title ?></p>
			<p>Code: <?php echo md5($id) ?></p>
			<p>Status: <span id="qstat"><?php echo ucwords($stat[$status])  ?></span></p>
			
		</div>
	</div>
	<div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-body" id="field">
					<?php if($status < 2): ?>
					<div id="qfield">
							<h4><i>Waiting for other students...</i></h4>
								<button class="btn large btn-success" type="button" id="start"><i class="fa fa-play"></i> Start Quiz Now</button>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header">
					<b>Participants/Students</b>
				</div>
				<div class="card-body">
					<div class="container-fluid">
					<div id="pfield"  style="width: 100%">
						<ul class="list-group">
						
						</ul>
					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="display: none" id="clone_q">
	<div class="col-md-12 q">
		<p>Question: <b id="qtxt"></b></p>
		<div class="row" id="opt-field">
			
		</div>
		<hr>
		<div class="row">
			<div class="col-md-12 text-center">
				<button class="btn-large btn btn-primary" type="button" id="next_question">Next Question</button>
				<button class="btn-large btn btn-primary" type="button" id="finish_quiz">Finish Quiz</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	websocket.onmessage = function(event) {
		var Data = JSON.parse(event.data);
		if(Data == null)
			return false;
		if(Data.type =='disconnected'){
			$.ajax({
				url:'ajax.php?action=disconnect_all',
				method:'POST',
				data:{id:'<?php echo $id ?>'},
				success:function(resp){
					if(resp == 1){
						$('#pfield ul').html('')
						websocket.send(JSON.stringify({'type':'request_quiz_connected','qid':'<?php echo md5($id) ?>'}))
					}
				}
			})
		}
		if(Data.type =='answer_question' && Data.code == '<?php echo $_GET['quiz'] ?>'){
			load_answered()
		}

		if(Data.type =='q_participate' && Data.code == '<?php echo $_GET['quiz'] ?>'){
			if($('#pfield li[data-id="'+Data.id+'"]').length <=0){
				var li = $('<li class="list-group-item participant"></li>')
				li.attr('data-id',Data.id)
				li.append(Data.name)
				$('#pfield ul').append(li)
			}
		}

		// console.log(Data)
	};
function load_question($qid,is_last=0){
	var question = <?php echo isset($quest) ? json_encode($quest) : '{}' ?>;
	var opt = <?php echo isset($opt) ? json_encode($opt) : '{}' ?>;
	var field = $('#clone_q .q').clone()
	field.find('#qtxt').html(question[$qid])
	var opt_html = '';
	Object.keys(opt[$qid]).map(k=>{
		opt_html +="<div class='col-md-6 mb-2 opt-item' data-id='"+opt[$qid][k].id+"'><div class='card "+(opt[$qid][k].is_right == 1 ? "bg-success" : "bg-danger")+"'><div class='card-body text-white'><b>"+opt[$qid][k].option_txt+"</b><span class='badge badge-primary float-right answered'>0</span></div></div></div>"
	})
	field.find('#next_question').attr('data-id',$qid)
	if(is_last == 1){
		field.find('#next_question').remove()
	}else{
		field.find('#finish_quiz').remove()
	}
	field.find('#opt-field').html(opt_html)
		$('#field').html(field)

load_answered()
$('#next_question').click(function(){
	start_load()
	$.ajax({
		url:'ajax.php?action=start_quiz_next',
		method:'POST',
		data:{qid:'<?php echo isset($id) ?$id : '' ?>',id:$(this).attr('data-id')},
		success:function(resp){
			resp=JSON.parse(resp)
			if(resp.status == 1){
				$('#qfield ul').html('')
				load_question(resp.question_id,resp.is_last)
				$('#qstat').html('<?php echo $stat[2] ?>')
					websocket.send(JSON.stringify({'type':'start_quiz','qid':'<?php echo md5($id) ?>',"start":resp.question_id}))
				end_load()
			}
		}
	})
})
$('#finish_quiz').click(function(){
	start_load()
	$.ajax({
		url:'ajax.php?action=finish_quiz',
		method:'POST',
		data:{qid:'<?php echo isset($id) ?$id : '' ?>'},
		success:function(resp){
			resp=JSON.parse(resp)
			if(resp.status == 1){
				location.replace("index.php?page=quiz_view&id=<?php echo $id ?>")
					websocket.send(JSON.stringify({'type':'finish_quiz','qid':'<?php echo md5($id) ?>'}))
				end_load()
			}
		}
	})
})

}
function load_answered(){
	$.ajax({
		url:'ajax.php?action=load_answered',
		method:'POST',
		data:{id:'<?php echo isset($id) ?$id : '' ?>'},
		success:function(resp){
			resp=JSON.parse(resp)
			if(Object.keys(resp).length > 0){
					$('.opt-item').find('.answered').html(0)
				Object.keys(resp).map(k=>{
					$('.opt-item[data-id="'+k+'"]').find('.answered').html(resp[k])
				})
				end_load()
			}
		}
	})
}

$('#start').click(function(){
	start_load()
	$.ajax({
		url:'ajax.php?action=start_quiz',
		method:'POST',
		data:{id:'<?php echo isset($id) ?$id : '' ?>'},
		success:function(resp){
			resp=JSON.parse(resp)
			if(resp.status == 1){
				$('#qfield ul').html('')
				load_question(resp.question_id)
				$('#qstat').html('<?php echo $stat[2] ?>')
					websocket.send(JSON.stringify({'type':'start_quiz','qid':'<?php echo md5($id) ?>',"start":resp.question_id}))
				end_load()
			}
		}
	})
})

$(document).ready(function(){
	if('<?php echo isset($status) ? $status : '0' ?>' == 2){
			load_question('<?php echo isset($question) ? $question:'' ?>','<?php echo $is_last ?>')
		}
		websocket.onopen = function(){
				websocket.send(JSON.stringify({'type':'request_quiz_connected','qid':'<?php echo md5($id) ?>'}))
			
		}
})
</script>