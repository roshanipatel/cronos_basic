<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('username')); ?>:</b>
	<?php echo CHtml::encode($data->username); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('email')); ?>:</b>
	<?php echo CHtml::encode($data->email); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('company_id')); ?>:</b>
	<?php echo CHtml::encode($data->company->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('role')); ?>:</b>
	<?php 
        echo CHtml::encode( Roles::toString( $data->role ) );
        ?>
        
        <b><?php echo CHtml::encode($data->getAttributeLabel('startcontract')); ?>:</b>
	<?php echo CHtml::encode(PHPUtils::removeHourPartFromDate($data->startcontract)); ?>
	<br />
        
        <b><?php echo CHtml::encode($data->getAttributeLabel('endcontract')); ?>:</b>
	<?php echo CHtml::encode(PHPUtils::removeHourPartFromDate($data->endcontract)); ?>
	<br />

</div>