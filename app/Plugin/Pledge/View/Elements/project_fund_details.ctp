<?php if ($project['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::OpenForIdea ){ ?>
<li class="navbar-btn txt-center-mbl">
	<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
		<?php echo $this->Html->cInt($project['Project']['project_fund_count']);?>
	</h3>
	<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
		<?php echo Configure::read('project.alt_name_for_backer_plural_caps');?>
	</p>
</li>
<?php } ?>
<?php if ($project['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::OpenForIdea ){ ?>
<li class="navbar-btn txt-center-mbl">
	<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
		<?php echo $this->Html->siteCurrencyFormat($this->Html->cCurrency($project['Project']['collected_amount'],false));?>
	</h3>
	<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
		<?php echo sprintf(__l('%s of'),Configure::read('project.alt_name_for_pledge_singular_caps')) . ' '.$this->Html->siteCurrencyFormat($this->Html->cCurrency($project['Project']['needed_amount'],false)) . ' ' . __l('goal'); ?> 
	</p>
</li>
<?php if($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::Pending){ ?>
	<?php if($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached){?>
<li class="navbar-btn txt-center-mbl">
		<?php if (!empty($project[0]['enddate']) && round($project[0]['enddate']) > 0) { ?>
			<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
				<?php echo $this->Html->cInt($project[0]['enddate']); ?>
			</h3>
		<?php } else { ?>
			<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold js-countdown">
				<?php echo $this->Html->cInt($project[0]['endhour'], false);?>
			</h3>
		<?php } ?>
		<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
			<?php echo (round($project[0]['enddate']) > 0) ?__l('Days to go') : __l('Hours to go'); ?>
		</p>
</li>
	<?php } ?>
<?php } ?>
<li class="media">
	<div class="pull-left no-float">
		<?php
			/* Chart block */
			$collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0;
			$needed__percentage = 0;
			if($collected_percentage < 100){
			$needed__percentage = 100-$collected_percentage;
			}
			echo $this->Html->image('http://chart.googleapis.com/chart?cht=p&amp;chd=t:'.$collected_percentage.','.$needed__percentage.'&amp;chs=70x70&amp;chco=00AFEF|C1C1BA&amp;chf=bg,s,FF000000', array('title' => __l('Collected') . ': ' . $collected_percentage.'%' , 'class' => 'h-center-blk'));
			/* Chart block ends*/
		?>
	</div>
	<div class="media-body navbar-btn no-float">
		<p class="h5 txt-center-mbl">
			<?php
			if (date('Y', strtotime($project['Project']['project_end_date'])) > date('Y') ) {
				$projectEndDate = strftime('%A %b %d %Y, %I:%M %p', strtotime($project['Project']['project_end_date']));
			} else {
				$projectEndDate = strftime('%A %b %d, %I:%M %p', strtotime($project['Project']['project_end_date']));
			}
			if (empty($project['Pledge']['is_allow_over_funding'])):
				$project_end_date = $project['Pledge']['project_fund_goal_reached_date'];
			else:
				$project_end_date = $project['Project']['project_end_date'];
			endif;
			if(!$project['Project']['is_admin_suspended']):
				if($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached):
					if ($project['Project']['needed_amount'] != 0):
						if ($project['Project']['collected_amount'] >= $project['Project']['needed_amount'] && !empty($project['Pledge']['is_allow_over_funding'])):
							echo sprintf(__l('Goal Reached, but it allows for over funding and this %s will be closed on'), Configure::read('project.alt_name_for_project_singular_small')) . ' <span title="' . strftime(Configure::read('site.datetime.tooltip'), strtotime($project['Project']['project_end_date'])) . '">' . $projectEndDate . ' ' . date('T') . '</span>';
						else:
							if($project['Project']['payment_method_id'] == ConstPaymentMethod::KiA) :
								echo sprintf(__l('This %s received all of its funded amount by %s'), Configure::read('project.alt_name_for_project_singular_small'), '<span title="' . strftime(Configure::read('site.datetime.tooltip'), strtotime($project['Project']['project_end_date'])) . '">' . $projectEndDate . ' ' . date('T') . '</span>');
							else:
								echo sprintf(__l('This %s will only be funded if at least %s is pledged by %s'), Configure::read('project.alt_name_for_project_singular_small'), $this->Html->siteCurrencyFormat($this->Html->cCurrency($project['Project']['needed_amount'], false)), '<span title="' . strftime(Configure::read('site.datetime.tooltip'), strtotime($project['Project']['project_end_date'])) . '">' . $projectEndDate . ' ' . date('T') . '</span>');
							endif;
						endif;
					endif;
				elseif($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed):
					if($project['Project']['payment_method_id'] == ConstPaymentMethod::KiA) :
						echo sprintf(__l('This %s received all of its funded amount %s'), Configure::read('project.alt_name_for_project_singular_small'), $this->Time->timeAgoInWords($project_end_date));
					else :
						echo sprintf(__l('This %s successfully raised its funding goal %s'), Configure::read('project.alt_name_for_project_singular_small'), $this->Time->timeAgoInWords($project_end_date));
					endif;
				endif;
			endif;
			?>
		</p>
	</div>
</li>
<hr class="hr-2px-drk-gray marg-top-5 marg-btom-5">
<?php } ?>
<?php if (isPluginEnabled('Idea') && $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForIdea) :?>
<li class="navbar-btn txt-center-mbl">
	<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
		<span class="js-idea-vote-count-<?php echo $this->Html->cInt($project['Project']['id'], false); ?> vote-count-value">
			<?php echo $this->Html->cInt($project['Project']['total_ratings']); ?>
		</span> 
	</h3>
	<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
		<?php echo __l('Votes'); ?>
	</p>
</li>
<li class="navbar-btn txt-center-mbl">
	<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
		<span class="b-color js-idea-voters-count"><?php echo $this->Html->cInt($project['Project']['project_rating_count']);?></span>
	</h3>
	<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
		<?php echo __l('Voters'); ?>
	</p>
</li>
<li class="navbar-btn txt-center-mbl">
	<h3 class="h1 list-group-item-text list-group-item-heading font-size-54 roboto-bold">
		<span class="js-idea-rating-count">
			<?php
				if($project['Project']['project_rating_count']!=0)
				{
					$average_rating = $project['Project']['total_ratings']/$project['Project']['project_rating_count'];
					echo $this->Html->cFloat($average_rating);
				}
				else
				{
					echo $this->Html->cFloat(0);
				}
			?>
		</span> 
	</h3>
	<p class="panel-title list-group-item-text list-group-item-heading clr-gray roboto-regular">
		<?php echo __l('Average votes'); ?>
	</p>
</li>
<li class="navbar-btn txt-center-mbl">
	<p class="h5 txt-center-mbl">
		<?php echo __l('This idea will only be listed for funding only if at least enough voters support it. Admin will move top votes ideas to projects based on number of votes.');?> 
	</p>
</li>
<hr class="hr-2px-drk-gray marg-top-5 marg-btom-5">
<?php endif;?>
<?php if ($project['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::OpenForIdea ){ 
		$more= ($project['Pledge']['pledge_type_id']!=ConstPledgeTypes::Fixed && $project['Pledge']['pledge_type_id']!=ConstPledgeTypes::Reward) ? __l('or more') : '';
		if (($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached || ConstPledgeProjectStatus::FundingClosed) && !$project['Project']['is_admin_suspended'] && !empty($project['ProjectReward']) && isPluginEnabled('ProjectRewards')) { 
?>
<li class="navbar-btn txt-center-mbl">
	<section class="thumbnail">
		<ul class="list-unstyled clearfix marg-top-20 ver-space">
			<?php
			$i=0;
			foreach ($project['ProjectReward'] as $projectReward){ 
				$i++;
				$limit_flag = 0;
				$reward_class = 'disabled';
				if ($projectReward['pledge_max_user_limit'] > $projectReward['project_fund_count'] || empty($projectReward['pledge_max_user_limit'])){
					$limit_flag = 1;
					$reward_class = '';
				}
				if(count($project['ProjectReward']) != $i){
					$reward_class.= "";
				}
			?>
			<li class="over-hide <?php echo $reward_class;?>">
				<section class="clearfix">
					<div class="clearfix">
						<h5 class="pull-left js-tooltip" title="<?php echo Configure::read('project.alt_name_for_pledge_singular_caps') .  ' ' . $this->Html->siteCurrencyFormat($this->Html->cCurrency($projectReward['pledge_amount'],false),false)  .' ' . __l('or more'); ?>">
							<?php echo Configure::read('project.alt_name_for_pledge_singular_caps') .  ' ' . $this->Html->siteCurrencyFormat($this->Html->cCurrency($projectReward['pledge_amount'], false), false)  .' ' . __l('or more'); ?>
						</h5>
						<?php $project_fund_count = !empty($projectReward['project_fund_count'])?$projectReward['project_fund_count']:'0'; ?>
						<div class="pull-right">
							<span class="label label-info pull-right">
								<?php echo !empty($projectReward['pledge_max_user_limit'])? __l('Limited').' ('.$project_fund_count.'/'.$projectReward['pledge_max_user_limit'].')': __l('Unlimited'); ?>
							</span>
						</div>
					</div>
					<p class="desc-cont over-hide">
						<span><?php echo $this->Html->cText($this->Html->truncate($projectReward['reward']), false);?></span>
					</p>
					<?php if(!empty($projectReward['estimated_delivery_date']) && !empty($projectReward['is_shipping']) && $projectReward['is_shipping']): ?>
						<p>
							<span><strong><?php echo __l('Estimated delivery date') . ': ';?></strong></span><?php echo $this->Html->cDate($projectReward['estimated_delivery_date']);?>
						</p>
					<?php endif; ?>
					<?php  echo $this->element('backers', array('project_id' => $project['Project']['id'], 'reward_id' => $projectReward['id'], 'project_type' => $project['ProjectType']['name'], 'backer_view' => 'compact')); ?>
					<?php if ($project['User']['id'] !=  $this->Auth->user('id')) :  ?>
						<?php if(($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached) && !$project['Project']['is_admin_suspended'] && !empty($limit_flag)):?>
							<section class="trans-bg">
								<?php
								if ($this->Auth->sessionValid()) {
								?>
									<div>
										<div class="clearfix pledge">
											<?php echo $this->Html->link(Configure::read('project.alt_name_for_pledge_singular_caps'), array('controller' => 'project_funds', 'action' => 'add', $project['Project']['id'], 'project_reward_id' => $projectReward['id']), array('title' => Configure::read('project.alt_name_for_pledge_singular_caps'),'class'=>'btn btn-primary js-tooltip js-no-pjax', 'escape' => false)); ?> 
										</div>
									</div>
								<?php
								} else {
								?>
									<div>
										<div class="clearfix pledge"> 
											<?php echo $this->Html->link(Configure::read('project.alt_name_for_pledge_singular_caps'), array('controller' => 'users', 'action' => 'login', '?' => 'f=project/' . $project['Project']['slug'], 'admin' => false), array('title' => Configure::read('project.alt_name_for_pledge_singular_caps'),'class'=>'btn btn-primary js-tooltip', 'escape' => false)); ?> 
										</div>
									</div>
								<?php
								}
								?>
							</section>
					<?php  endif;
					endif; ?>
				</section>
			</li>
			<?php } ?>
		</ul>
	</section>
	<?php } ?>
	<?php }?>
</li>
<li class="navbar-btn media page-header">
	<div class="pull-right no-float">
		<div class="img-contain-110 img-circle center-block">
			<?php echo $this->Html->getUserAvatar($project['User'], 'user_thumb'); ?> 
		</div>
	</div>
	<div class="media-body no-float">
		<h3 class="h4 txt-center-mbl list-group-item-text list-group-item-heading roboto-bold">
			<?php echo $this->Html->link($this->Html->cText($project['User']['username']), array('controller'=> 'users', 'action' => 'view', $project['User']['username']), array('title' => $project['User']['username'], 'escape' => false, 'itemtype' =>'http://schema.org/Organization', 'itemscope' => '', 'itemprop' =>'name'));?>
		</h3>
		<p class="h4 txt-center-mbl" itemscope itemtype="http://schema.org/interactionCount" itemprop="attendees">
			<span class="h5 clr-gray roboto-regular">
				<?php echo $project_count;?><?php echo __l(' Projects posted'); ?>
				<?php echo $this->Html->cInt($project['User']['unique_project_fund_count'], false);?><?php echo __l(' Projects funded'); ?>
				<?php if(isPluginEnabled('ProjectFollowers')):?>
				<?php echo __l('Following ');?><?php echo $project_following_count;?><?php echo __l(' project(s)'); ?>
				<?php endif;?>
			</span>
		</p>
	</div>
	<hr class="hr-2px-drk-gray marg-top-30 marg-btom-5">
</li>
<?php /*if (isPluginEnabled('ProjectFollowers')) { ?>
<section class="clearfix">
<?php  echo $this->element('followers', array('project_id' => $project['Project']['id']), array('plugin' => 'ProjectFollowers')); ?>
</section>
<?php } ?>
<section class="clearfix"> <?php echo $this->element('project-activities', array('project_id' => $project['Project']['id'], 'project_type'=>$project['Project']['project_type_id'], 'cache' => array('config' => 'sec', 'key' => $project['Project']['id'])));?> </section>
<?php if (Configure::read('widget.project_script')) { ?>
<section class="clearfix">
<div class="text-center clearfix"> <?php echo Configure::read('widget.project_script'); ?> </div>
</section>
<?php } */?>

