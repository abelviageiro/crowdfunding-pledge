
	<div class="clearfix" itemtype="http://schema.org/Product" itemscope>
		<div itemprop="Name">
			<?php echo $this->Html->link($this->Html->image('pledge.png'), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'pledge' , 'admin' => false), array('class'=> 'zoom-plus js-no-pjax','title' => __l(Configure::read('project.alt_name_for_pledge_singular_caps')), 'escape' => false));?>
			<h3 class="h4 zoom-plus">
				<?php echo $this->Html->link(__l(Configure::read('project.alt_name_for_pledge_singular_caps')), array('controller' => 'projects', 'action' => 'discover', 'project_type'=> 'pledge' , 'admin' => false), array('class'=> 'text-uppercase clr-blu txt js-no-pjax','title' => __l(Configure::read('project.alt_name_for_pledge_singular_caps'))));?>
			</h3>
		</div>
		<p class="h4" itemprop="description"><?php echo sprintf(__l("In %s %s, amount is captured by end date and may offer %s."), Configure::read('project.alt_name_for_pledge_singular_small'), Configure::read('project.alt_name_for_project_plural_small'), Configure::read('project.alt_name_for_reward_plural_small')); ?></p>
	</div>

