<?php /* SVN: $Id: index.ctp 2879 2010-08-27 11:08:48Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>

<div class="js-response space hor-mspace">
	<?php } ?>
	<div class="clearfix space" id="js-pledge-scroll" itemtype="http://schema.org/Product" itemscope>
		<div class="pledge-status text-info" itemprop="Name"> <span class="ver-space"><?php echo $this->Html->image('pledge-hand.png', array('width' => 50, 'height' => 50)); ?></span><span class="h3 no-mar"><?php echo __l(Configure::read('project.alt_name_for_pledge_singular_caps')); ?></span></div>
		<!--<h3 class="pledgec text-info navbar-btn"><?php echo sprintf(__l('My').' %s', Configure::read('project.alt_name_for_pledge_plural_caps')); ?></h3>-->
	</div>
	<div class="clearfix">
		<div class="alert alert-info"><span><?php echo __l('Print reward voucher option will be available in funded projects'); ?></span></div>
		<?php
			if ($is_wallet_enabled) {
			  $link1 = __l('Backed');
			  $link2 = __l('Refunded');
			  $link3 = __l('Funded');
			  $link ="Refunded";
			} else {
			  $link1 = __l('Authorized');
			  $link2 = __l('Voided');
			  $link3 = __l('Captured');
			  $link ="Voided";
			}
		?>
		<ul class="filter-list-block list-inline">
			<li> <?php echo $this->Html->link('<span class="badge badge-info"><span><strong>'.$this->Html->cInt($fund_count,false).'</strong></span></span><span class="show">' .__l('All'). '</span>', array('controller'=>'pledges','action'=>'myfunds', 'status' => 'all'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
			<li> <?php echo $this->Html->link('<span class="badge badge-warning"><span><strong>'.$this->Html->cInt($backed_count,false).'</strong></span></span><span class="show">' .$link1. '</span>', array('controller'=>'pledges','action'=>'myfunds'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
			<li> <?php echo $this->Html->link('<span class="badge badge-danger"><span><strong>'.$this->Html->cInt($refunded_count,false).'</strong></span></span><span class="show">' .$link2. '</span>', array('controller'=>'pledges','action'=>'myfunds','status'=>'refunded'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
			<li> <?php echo $this->Html->link('<span class="badge badge-success"><span><strong>'.$this->Html->cInt($paid_count,false).'</strong></span></span><span class="show">' .$link3. '</span>', array('controller'=>'pledges','action'=>'myfunds','status'=>'paid'), array('class' => 'js-filter js-no-pjax pull-left', 'escape' => false));?> </li>
		</ul>
	</div>
	<?php  echo $this->element('paging_counter'); ?>
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover panel">
			<tr>
				<th class="text-center"><?php echo __l('Actions');?></th>
				<th class="js-filter text-left"><?php echo __l(Configure::read('project.alt_name_for_project_singular_caps'));?></th>
				<th class="js-filter text-center"><div class="js-filter"><?php echo __l('Collected');?></div>
				/ <?php echo __l('Needed Amount'). ' (' . Configure::read('site.currency') . ')';?></th>
				<th class="js-filter text-right"><?php echo __l('Amount') . ' (' . Configure::read('site.currency') . ')' ;?></th>
				<th class="text-center"><?php echo __l('Payment Status');?></th>
				<th class="js-filter js-no-pjax text-center"><?php echo sprintf(__l('%s On'),Configure::read('project.alt_name_for_pledge_past_tense_caps'));?></th>
				<?php if (isPluginEnabled('ProjectRewards')) { ?>
				<th class="text-left"><?php echo __l(Configure::read('project.alt_name_for_reward_singular_caps'));?></th>
				<th class="text-left"><?php echo sprintf(__l('%s Status'), Configure::read('project.alt_name_for_reward_singular_caps'));?></th>
				<?php } ?>
			</tr>
			<?php
			if (!empty($projectFunds)):
			  $i = 0;
			  foreach ($projectFunds as $projectFund):
				$class = null;
				if ($i++ % 2 == 0) {
				  $class = ' class="altrow"';
				}
				$refund = __l('Funded');
				  if($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding) {
					$refund = Configure::read('project.alt_name_for_pledge_past_tense_caps');
				  } else {
					$refund = __l('Funded');
				  }
				if ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
				  $refund = __l('Failed');
				  $class = ' class="altrow hide js-faild"';
				} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
				  $refund = __l('Canceled');
				  $class = ' class="altrow hide js-faild"';
				} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Canceled) {
					$refund = 'Refunded';
				  if($projectFund['ProjectFund']['is_canceled_from_gateway']) {
					$refund.= '(From Gateway)';
				  }
				  $refund = $refund;
				}
			?>
			<tr <?php echo $class;?>>
				<td class="col-md-1 text-center">
					<div class="dropdown"> <a href="#" title="Actions" data-toggle="dropdown" class="fa fa-cog fa-fw dropdown-toggle js-no-pjax"><span class="hide">Action</span></a>
						<ul class="list-unstyled dropdown-menu text-left clearfix">
						<?php
						  if ($projectFund['ProjectFund']['user_id'] == $this->Auth->user('id') && (Configure::read('Project.is_allow_fund_cancel_by_funder')) && (strtotime('+'.Configure::read('Project.minimum_days_before_fund_cancel').' days') < strtotime($projectFund['Project']['project_end_date'].'23:59:59'))  && $projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding): ?>
						<?php  if ($projectFund['ProjectFund']['project_fund_status_id'] != ConstProjectFundStatus::Expired && $projectFund['ProjectFund']['project_fund_status_id'] != ConstProjectFundStatus::Canceled) :
						  $link = sprintf(__l('Cancel %s'), Configure::read('project.alt_name_for_pledge_singular_caps'));
							  $type = 'cancel'; ?>
							<li> <?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.$link, array('controller'=> 'project_funds', 'action' => 'edit_fund', 'project_fund' => $projectFund['ProjectFund']['id'], 'type' => $type, 'return_page' => 'mydonations'), array('escape' => false,'class' => 'cancel js-confirm','title'=>$link,'escape'=>false)); ?> 
							</li>
							<?php  endif;
							endif; ?>
							<li> <?php echo $this->Html->link('<i class="fa fa-user fa-fw"></i>'.sprintf(__l('Contact %s'), Configure::read('project.alt_name_for_pledge_project_owner_singular_small')), array('controller' => 'projects', 'action' => 'view', $projectFund['Project']['slug'] . '#comments'), array('class' => 'cboxelement msg', 'escape' => false,'title' => sprintf(__l('Contact %s'), Configure::read('project.alt_name_for_project_owner_singular_small')))); ?> 
							</li>
							<?php if (isPluginEnabled('ProjectRewards')) { ?>
							<?php if (!empty($projectFund['ProjectFund']['project_reward_id']) && $projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed && empty($projectFund['ProjectFund']['is_given']) ): ?>
							<li>
								<?php  echo $this->Html->link('<i class="fa fa-print"></i><span>'.__l('Print voucher').'</span>', array('controller' => 'project_funds', 'action' => 'view', $projectFund['ProjectFund']['id'],'type'=>'print'), array('escape' => false,'target'=>'_blank','title'=>__l('Print voucher'), 'class' => 'print-voucher', 'escape'=>false)); ?>
							</li>
							<?php endif; ?>
							<?php } ?>
						</ul>
					</div>
				</td>
				<td class="text-left"><?php
						if($is_wallet_enabled) {
							$project_status = $projectFund['Project']['Pledge']['PledgeProjectStatus']['name'];
						} else {
						$project_status = str_replace("Refunded","Voided",$projectFund['Project']['Pledge']['PledgeProjectStatus']['name']);
						}
					?>
				<i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square fa-fw project-status-<?php echo $this->Html->cInt($projectFund['Project']['Pledge']['pledge_project_status_id'], false);?>"></i> <?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects','action' => 'view', $projectFund['Project']['slug']), array('class' => 'cboxelement', 'escape' => false,'title'=> $this->Html->cText($projectFund['Project']['name'],false)));?> </td>
				<td class="text-right pledge"><?php $collected_percentage = ($projectFund['Project']['collected_percentage']) ? $projectFund['Project']['collected_percentage'] : 0; ?>
				<div class="progress">
					<div style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" class="progress-bar"></div>
				</div>
				<p class="text-center"><?php echo $this->Html->cCurrency($projectFund['Project']['collected_amount']); ?> / <?php echo $this->Html->cCurrency($projectFund['Project']['needed_amount']); ?></p></td>
				<td class="text-right"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?></td>
				<td class="text-center"><?php echo $this->Html->cText($refund);?></td>
				<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($projectFund['ProjectFund']['created']);?></td>
				<?php if (isPluginEnabled('ProjectRewards')) { ?>
				<td class="text-left"><span class="rewarded"><?php echo $this->Html->cText(!empty($projectFund['ProjectReward']['reward'])?$this->Html->truncate($projectFund['ProjectReward']['reward'], 150): sprintf(__l('No %s selected'), Configure::read('project.alt_name_for_reward_singular_small')));?></span>
				<?php
					if (!empty($projectFund['ProjectReward']['reward']) && !empty($projectFund['ProjectReward']['is_shipping']) && $projectFund['ProjectReward']['is_shipping']) {
					echo '<span class="help"><strong>'.__l('Estimated Delivery Date: ').$this->Html->cDate($projectFund['ProjectReward']['estimated_delivery_date']).'</strong></span>';
					}
				?>
				</td>
				<td class="text-center"><?php
					$reward = '';
					if (!empty($projectFund['ProjectFund']['project_reward_id']) && ($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed || $projectFund['Project']['Pledge']['pledge_project_status_id']== ConstPledgeProjectStatus::GoalReached) && empty($projectFund['ProjectFund']['is_given'])):
					$reward = __l('Not received');?>
					<?php endif; ?>
					<?php if (!empty($projectFund['ProjectFund']['is_given'])):
					$reward = __l('Received');?>
					<?php endif; ?>
					<?php if(empty($reward)) {
					$reward = __l('n/a');
					}?>
					<p><span><?php echo $reward; ?></span></p></td>
					<?php } ?>
			</tr>
			<?php
				endforeach;
				else:
			?>
			<tr>
				<td colspan="8">
					<div class="text-center no-items">
						<p><?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_pledge_plural_caps'));?></p>
					</div>
				</td>
			</tr>
			<?php
				endif;
			?>
		</table>
	</div>
	<?php if (!empty($projectFunds)) { ?>
	<div class="clearfix">
		<div class=" pull-right paging js-pagination js-no-pjax {'scroll':'js-pledge-scroll'}"> <?php echo $this->element('paging_links'); ?> </div>
	</div>
	<?php } ?>
	<?php if (!$this->request->params['isAjax']) { ?>
</div>
<?php } ?>
