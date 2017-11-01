<div class="form-group">
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label?>: </label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<div id="<?php echo $e->id;?>-static" class="well static-well">
			<?php echo $e->value; ?>
			<input id="<?php echo $e->id;?>" class="form-control" type="hidden" name="<?php echo $e->name;?>" value="<?php echo $e->value; ?>">
		</div>
	</div>
	<?php if($form->lang($e->name . '_hint')) : ?>
		<span class="glyphicon glyphicon-info-sign hint" data-toggle="tooltip"  title="<?php echo $form->lang($e->name . '_hint'); ?>"></span>
	<?php endif; ?>
	<div id="<?php echo $e->id;?>_error" class="error"></div>
</div>