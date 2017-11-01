<div class="form-group">
	<div class="checkgroup">
		<label for="<?php echo $e->name;?>" class="col-xs-12 col-sm-4 col-md-3 col-lg-2 control-label"><?php echo $e->label?>: </label>
		<div class="col-xs-12 col-sm-7 col-md-8 col-lg-6">

			<ul ng-if=" filter.type == 'checkbox' " class="list-unstyled animate-container">
				<?php foreach($e->options as $key => $option):?>
					<li>
						<!-- <input type="checkbox" name="{{ filter.key }}" id="cb-{{ filter.key }}-{{ option.key }}" value="{{ option.key }}"> -->
						<input id="ufo-<?php echo $e->form->name;?>-<?php echo $e->name;?>-<?php echo $key;?>" type="checkbox" name="<?php echo $e->name;?>[<?php echo $key;?>]" value="<?php echo $key; ?>"<?php if(in_array($key, $e->selected)):?> checked<?php endif;?>>
						<label class="checkbox" for="ufo-<?php echo $e->form->name;?>-<?php echo $e->name;?>-<?php echo $key;?>" title="<?php echo $option;?>"><?php echo $option;?></label>
					</li>
				<?php endforeach; ?>
			</ul>
			<div id="<?php echo $e->id;?>_error" class="error"><?php echo $e->error_text;?></div>
		</div>		
	</div>
</div>