<div class="form-group">
    <div class="col-sm-offset-2 col-lg-9">
		<div class="checkbox">
			<label>
				<input id="<?php echo $e->id;?>" type="checkbox" name="<?php echo $e->name;?>" value="<?php echo $e->name; ?>"<?php if($e->value == $e->checked_value):?> checked<?php endif;?>>
				<?php echo $e->label;?>
			</label>	
			<div id="<?php echo $e->id;?>_error" class="error"><?php echo $e->error_text;?></div>
		</div> 
	</div>
</div>