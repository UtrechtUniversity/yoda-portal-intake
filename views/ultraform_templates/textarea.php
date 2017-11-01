<div class="form-group">
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label?>: </label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<textarea id="<?php echo $e->id;?>" class="form-control" name="<?php echo $e->name;?>" placeholder="<?php echo $e->placeholder;?>" rows="4" cols="50"><?php echo $e->value; ?></textarea>
	</div>
	<?php if($form->lang($e->name . '_hint')) : ?>
		<span class="glyphicon glyphicon-info-sign hint" data-toggle="tooltip"  title="<?php echo $form->lang($e->name . '_hint'); ?>"></span>
	<?php endif; ?>
	<div id="<?php echo $e->id;?>_error" class="error"><?php echo $e->error_text;?></div>
</div>