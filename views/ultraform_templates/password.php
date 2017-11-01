<div class="form-group">
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label;?>: </label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<input id="<?php echo $e->id;?>" class="form-control" type="password" placeholder="<?php echo $e->placeholder;?>" name="<?php echo $e->name;?>" value="<?php echo $e->value; ?>" autocomplete="off">
	</div>
	<div id="<?php echo $e->id;?>_error" class="col-xs-12 col-sm-7 col-md-8 col-lg-6 error col-sm-offset-2"><?php echo $e->error_text;?></div>
</div>