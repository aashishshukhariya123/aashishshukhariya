<?php include 'db_connect.php' ?>
<?php
session_start();
if(isset($_GET['id'])){
$qry = $conn->query("SELECT * FROM quiz_list where id= ".$_GET['id']);
foreach($qry->fetch_array() as $k => $val){
	$$k=$val;
}
}
?>
<div class="container-fluid">
	<form action="" id="manage-quiz">
		<div id="msg"></div>
			<div class="form-group">
				<label>Title</label>
				<input type="hidden" name="id" value="<?php echo isset($id) ? $id :'' ?>"/>
				<input type="text" name="title" required="required" class="form-control" value="<?php echo isset($title) ? $title : '' ?>" />
			</div>
			<?php if($_SESSION['login_type'] == 1): ?>
			<div class="form-group">
				<label>Teacher</label>
				<select name="user_id" required="required" class="form-control" />
				<option value="" selected="" disabled="">Select Here</option>
				<?php
					$qry = $conn->query("SELECT * from users where type = 2 order by name asc");
					while($row= $qry->fetch_assoc()){
				?>
					<option value="<?php echo $row['id'] ?>" <?php echo isset($user_id) && $user_id == $row['id'] ? 'selected' :'' ?>><?php echo $row['name'] ?></option>
				<?php } ?>
				</select>
			</div>
			<?php else: ?>
				<input type="hidden" name="user_id" value="<?php echo $_SESSION['login_id'] ?>" />
			<?php endif; ?>
	</form>
</div>
<script>
	$('#manage-quiz').submit(function(e){
		e.preventDefault()
		start_load()
		$('#msg').html('')
		$.ajax({
			url:'ajax.php?action=save_quiz',
			data: new FormData($(this)[0]),
		    cache: false,
		    contentType: false,
		    processData: false,
		    method: 'POST',
		    type: 'POST',
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully added",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
				else if(resp==2){
					$('#msg').html("<div class='alert alert-danger'>Title already exist.</div>")
					end_load()

				}
			}
		})
	})
</script>