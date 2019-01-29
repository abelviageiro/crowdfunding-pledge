<div class="col-xs-12 col-sm-6 col-md-3 start-projects">
	<div class="bg-light-gray">
		<div class="img-contain"><?php echo $this->Html->image('pledge.png'); ?></div>
		<?php echo $this->Html->link(__l(Configure::read('project.alt_name_for_pledge_singular_caps')). " " . __l(Configure::read('project.alt_name_for_project_singular_caps')), array('controller' => 'projects', 'action' => 'add', 'project_type'=>'pledge', 'admin' => false), array('title' => __l(Configure::read('project.alt_name_for_pledge_singular_caps')). " " . __l(Configure::read('project.alt_name_for_project_singular_caps')),'class' => 'js-tooltip h3 text-info', 'escape' => false));?>
		<p class="navbar-btn"><?php echo __l('People initially pledge. Amount is captured by end date. May offer rewards.'); ?></p>
	</div>
</div>