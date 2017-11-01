<div class="form-group <?php if($e->error_text != ''){echo 'error-div';} ?>">
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label; ?>:</label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<input id="<?php echo $e->id;?>" class="form-control" type="text" name="<?php echo $e->name;?>" placeholder="<?php echo $e->placeholder;?>" value="<?php echo $e->value; ?>">
	</div>
	<div id="<?php echo $e->id;?>_error" class="col-xs-12 col-sm-7 col-md-8 col-lg-6 error col-sm-offset-2"><?php echo $e->error_text;?></div>
</div>