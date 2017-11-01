<?php $hidden = array('ufo-formname' => $e->form->name, $e->name => 'form' );?>
<?php echo form_open('', array('id' => 'ufo-' . $e->form->name, 'class' => 'form-horizontal', 'role' => 'form'), $hidden); ?>
