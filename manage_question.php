<?php
session_start();
include('db_connect.php');
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM questions where id =  ".$_GET['id']);

	foreach($qry->fetch_array() as $k => $v){
		$data['qdata'][$k] = $v;
	}
	$qry2 = $conn->query("SELECT * FROM question_opt where question_id =  ".$_GET['id']);
	while($row = $qry2->fetch_assoc()){
		$data['odata'][] = $row;
	}
}
?>

<div class="container-fluid">
	<form id='question-frm'>
		<div class ="modal-body">
			<div id="msg"></div>
			<div class="row form-group">
				<label>Question</label>
				<input type="hidden" name="qid" value="<?php echo $_GET['qid'] ?>" />
				<input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '' ?>" />
				<textarea rows='3' name="question" required="required" class="form-control" ><?php echo isset($data['qdata']['question']) ? $data['qdata']['question'] :'' ?></textarea>
			</div>
			<div class="row form-group">
				<div class="col-md-5">
					<label>Question Points</label>
					<input type="number" name="points"  class="form-control text-right" value="<?php echo isset($data['qdata']['points']) ? $data['qdata']['points'] :'' ?>"/>
				</div>
			</div>
				<label>Options:</label>

			<div class="row form-group">
				<div class="col-md-6">
					<textarea rows="2" name ="question_opt[0]" required="" class="form-control" ><?php echo isset($data['odata'][0]) ? $data['odata'][0]['option_txt'] :'' ?></textarea>
					<span>
					<label><input type="radio" name="is_right[0]" class="is_right" value="1" <?php echo isset($data['odata'][0]['is_right']) && $data['odata'][0]['is_right'] == 1 ? 'checked' :'' ?>> <small>Question Answer</small></label>
					</span>
					<br>
					<textarea rows="2" name ="question_opt[1]" required="" class="form-control" ><?php echo isset($data['odata'][1]) ? $data['odata'][1]['option_txt'] :'' ?></textarea>
					<label><input type="radio" name="is_right[1]" class="is_right" value="1"  <?php echo isset($data['odata'][1]) && $data['odata'][1]['is_right'] == 1 ? 'checked' :'' ?>> <small>Question Answer</small></label>
				</div>
				<div class="col-md-6">
					<textarea rows="2" name ="question_opt[2]" required="" class="form-control" ><?php echo isset($data['odata'][2]) ? $data['odata'][2]['option_txt'] :'' ?></textarea>
					<label><input type="radio" name="is_right[2]" class="is_right" value="1"  <?php echo isset($data['odata'][2]) && $data['odata'][2]['is_right'] == 1 ? 'checked' :'' ?>> <small>Question Answer</small></label>
					<br>
					<textarea rows="2" name ="question_opt[3]" required="" class="form-control" ><?php echo isset($data['odata'][3]) ? $data['odata'][3]['option_txt'] :'' ?></textarea>
					<label><input type="radio" name="is_right[3]" class="is_right" value="1"  <?php echo isset($data['odata'][3]) && $data['odata'][3]['is_right'] == 1 ? 'checked' :'' ?>> <small>Question Answer</small></label>
				</div>
			</div>
			
		</div>
		
	</form>
</div>
<script>
	$('.is_right').each(function(){
			$(this).click(function(){
				$('.is_right').prop('checked',false);
				$(this).prop('checked',true);
			})
		})
	$('#question-frm').submit(function(e){
		e.preventDefault()
		start_load()
		$('#msg').html('')
		$.ajax({
			url:'ajax.php?action=save_question',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully saved",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	})
</script>