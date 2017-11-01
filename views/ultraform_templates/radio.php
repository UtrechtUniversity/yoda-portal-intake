<div class="form-group">	
	<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label?>: </label>
	<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">
		<?php foreach($e->options as $key => $option):?>
		
			<div class="radio">
				<label>
					<input id="ufo-<?php echo $e->formname;?>-<?php echo $e->name;?>-<?php echo $key;?>" type="radio" name="<?php echo $e->name;?>" value="<?php echo $key;?>"<?php if($value == $key):?> checked<?php endif;?>>
					<?php echo $option;?>
				</label> 
			</div>
		
		<?php endforeach;?>
		<div id="<?php echo $e->id;?>_error" class="error"><?php echo $e->error_text;?></div>
	</div>
</div>