<?php $collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0 ;?>
<div class="progress list-group-item-text">
	<div class="progress-bar progress-bar-info" role="progressbar" style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo $collected_percentage;?>"></div>
</div>
<div class="row">
	<?php if($project['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::Pending): ?>
		<div class="col-xs-4">
			<h3 class="h3 navbar-btn list-group-item-text">
				<strong><?php echo $this->Html->cInt($collected_percentage);?><?php echo '%';?></strong>
			</h3>
			<p class="grayc h6 list-group-item-heading htruncate"><?php echo __l('funded'); ?></p>
		</div>
	<?php endif; ?>
	<div class="col-xs-4">
		<div class="row">
		<h3 class="h3 navbar-btn list-group-item-text htruncate">
			<strong><?php echo $this->Html->siteCurrencyFormat($this->Html->cCurrency($project['Project']['collected_amount'],false)); ?></strong>
		</h3>
		<p class="grayc h6 list-group-item-heading htruncate"><?php echo Configure::read('project.alt_name_for_pledge_past_tense_small'); ?></p>
		</div>	
	</div>
	<div class="col-xs-4 text-right">
		<?php
		$days = '';
		$display_text = '';
		if ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed):
		$display_text = __l('ended on');
		if (empty($project['Pledge']['is_allow_over_funding'])):
		$days = $this->Html->cDateTimeHighlight($project['Pledge']['project_fund_goal_reached_date'], false);
		else:
		$days = $this->Html->cDateTimeHighlight($project['Project']['project_end_date'], false);
		endif;
		elseif ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached):
		$display_text = __l('funded on');		  
		$days = $this->Html->cDateTimeHighlight($project['Pledge']['project_fund_goal_reached_date'], false);
		elseif($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingExpired):
		$display_text = __l('expired');
		$days = $this->Html->cDateTimeHighlight($project['Project']['project_end_date'], false);
		elseif($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::ProjectCanceled):
		$display_text = __l('Canceled');
		$days = $this->Html->cDateTimeHighlight($project['Project']['project_cancelled_date'], false);
		else:
		if ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding ||  $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached):
		$display_text = (round($project[0]['enddate']) >0) ? __l('days to go') : __l('hours to go');
		if ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding ||  $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached):
		if(!empty($project[0]['enddate']) && round($project[0]['enddate']) >0):
		$days = $this->Html->cInt($project[0]['enddate'], false);
		else:
		$countdown = 1;
		$days = intval(strtotime($project['Project']['project_end_date'] . ' 23:59:59') - time());
		endif;
		endif;
		endif;
		endif;
		?>
		<h3 class="h3 navbar-btn list-group-item-text htruncate">
			<?php if (!empty($countdown)): ?>
			<div class="js-countdown">&nbsp;</div>
			<span class="js-time hide"><?php echo $this->Html->cInt($days, false);?></span>
			<?php else: ?>
			<span class="c" title="<?php echo $days;?>"><?php echo $days;?></span>
			<?php endif; ?>
		</h3>
		<p class="grayc h6 list-group-item-heading htruncate"><?php echo $display_text; ?></p>
	</div>
</div>

