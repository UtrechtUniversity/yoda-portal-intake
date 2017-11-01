<div class="form-group">
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label?>: </label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<select multiple id="<?php echo $e->id;?>" class="select-style form-control" name="<?php echo $e->name;?>">
			<?php foreach($e->options as $key => $option):?>
			<option value="<?php echo $key;?>"<?php if($value == $key):?> selected<?php endif;?>><?php echo $option;?></option>
		  	<?php endforeach;?>
		</select>
	</div>
	<?php if($form->lang($e->name . '_hint')) : ?>
	
		<span class="glyphicon glyphicon-info-sign hint" data-toggle="tooltip"  title="<?php echo $form->lang($e->name . '_hint'); ?>"></span>
	
	<?php endif; ?>
	<div id="<?php echo $e->id;?>_error" class="error"><?php echo $e->error_text;?></div>
</div>