<div class="main-admn-usr-lst js-response">
	<?php if(empty($this->request->params['named']['view_type'])) : ?>
	<div class="row bg-primary">		
		<ul class="list-inline sec-1 navbar-btn">
			<?php
			if ($is_wallet_enabled) {
			$project_status = "Refunded";
			} else {
			$project_status = "Voided";
			}
			if ($is_wallet_enabled) {
			$link1 = "Backed";
			$link2 = "Refunded";
			$link3 = 'Funded';
			$link ="Refunded";
			} else {
			$link1 = "Authorized";
			$link2 = "Voided";
			$link3 = 'Captured';
			$link ="Voided";
			}
			$project_percentage = '';
			$project_stat = '';
			$all = $fund_count;
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center ste-usr">'.$this->Html->cInt($authorized_count, false).'</span><span>' .__l('Authorized'). '</span>', array('controller'=>'pledges','action'=>'funds', 'type' => 'authorized'), array('escape' => false, 'title' => __l('Authorized')));?>
					</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_stat .= (!empty($project_stat)) ? '|'.$link1 : $link1;
			$project_percentage .= round((empty($authorized_count)) ? 0 : ( ($authorized_count / $all) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center pri-bt-usr">'.$this->Html->cInt($captured_count, false).'</span><span>' .__l('Captured'). '</span>', array('controller'=>'pledges','action'=>'funds', 'type' => 'captured'), array('escape' => false, 'title' => __l('Captured')));?>
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_stat .= (!empty($project_stat)) ? '|'.$link2 : $link2;
			$project_percentage .= round((empty($captured_count)) ? 0 : ( ($captured_count / $all) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block act-usr text-center">'.$this->Html->cInt($voided_count, false).'</span><span>' .__l('Voided'). '</span>', array('controller'=>'pledges','action'=>'funds', 'type' => 'voided'), array('escape' => false, 'title' => __l('Voided')));?>
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_stat .= (!empty($project_stat)) ? '|'.$link3 : $link3;
			$project_percentage .= round((empty($voided_count)) ? 0 : ( ($voided_count / $all) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center ina-usr">'.$this->Html->cInt($failed_count, false).'</span><span>' .__l('Failed'). '</span>', array('controller'=>'pledges','action'=>'funds', 'type' => 'failed'), array('escape' => false, 'title' => __l('Failed')));?>
				</div>
			</li>
			<?php
			//for small pie chart
			$project_percentage .= ($project_percentage != '') ? ',' : '';
			$project_stat .= (!empty($project_stat)) ? '|'.$link : $link;
			$project_percentage .= round((empty($failed_count)) ? 0 : ( ($failed_count / $all) * 100 ));
			?>
			<li>
				<div class="well-sm">
					<?php echo $this->Html->link('<span class="img-circle img-thumbnail bg-sucess img-wdt center-block text-center opn-i-usr">'.$this->Html->cInt($all, false).'</span><span>' .__l('All'). '</span>', array('controller'=>'pledges', 'action'=>'funds'), array('class' => 'text-center', 'escape' => false));?>
				</div>
			</li>
			<li class="navbar-right">
				<?php echo $this->Html->image('http://chart.googleapis.com/chart?cht=p&amp;chd=t:'.$project_percentage.'&amp;chs=120x120&amp;chco=FFAD46|468847|8D92D6|FD66B5&amp;chf=bg,s,FF000000'); ?>
			</li>
		</ul>		
	</div>
	<div class="clearfix pledge">		
		<div class="navbar-btn">
			<h3>
				<i class="fa fa-th-list fa-fw"></i> <?php echo __l('List');?>
			</h3>
			<?php
			$placeholder = __l('Search');
			if (!empty($this->request->params['named']['q'])) {
			$placeholder = $this->request->params['named']['q'];
			}
			?>
			<ul class="list-unstyled clearfix">
				<li class="pull-left"> 
					<p><?php echo $this->element('paging_counter');?></p>
				</li>
				<li class="pull-right"> 
					<div class="srch-adon">
						<?php echo $this->Form->create('Pledge' ,array('url' => array('controller' => 'pledges','action' => 'funds')), array('type' => 'get', 'class' => 'form-search')); ?>
						<span class="form-control-feedback " id="basic-addon1" aria-hidden="true"><i class="fa fa-search text-default"></i></span>
						<?php echo $this->Form->input('q', array('label' => false,' placeholder' => __l('Search'), 'class' => 'form-control')); ?>
						<div class="hide">
						<?php echo $this->Form->submit(__l('Search'));?>
						</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</li>
			</ul>
		</div>		
		<?php endif; ?>		
		<div class="table-responsive">
			<table class="table table-striped table-bordered">
				<thead class="h5">
					<tr>
						<th class="text-center table-action-width"><?php echo __l('Action');?></th>
						<?php if(empty($this->request->params['named']['view_type'])) : ?>
						<th class="text-left"><div><?php echo $this->Paginator->sort('Project.name', __l(Configure::read('project.alt_name_for_project_singular_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<?php endif;?>
						<th class="text-left"><div><?php echo $this->Paginator->sort('User.username', __l(Configure::read('project.alt_name_for_backer_singular_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th class="text-center"><div><?php echo __l('Paid Amount') . ' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('amount', sprintf(__l('Amount to %s'), Configure::read('project.alt_name_for_pledge_project_owner_singular_caps')), array('class' => 'js-no-pjax js-filter')).' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('site_fee', __l('Site Commission'), array('class' => 'js-no-pjax js-filter')).' ('.Configure::read('site.currency').')';?></div></th>
						<th class="text-center"><div><?php echo $this->Paginator->sort('created', sprintf(__l('%s On'), Configure::read('project.alt_name_for_pledge_past_tense_caps')), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th><div><?php echo $this->Paginator->sort('Status', __l('Status'));?></div></th>
						<?php if (isPluginEnabled('ProjectRewards')) { ?>
						<th class="text-left"><div><?php echo $this->Paginator->sort('project_reward_id', __l('Reward'), array('class' => 'js-no-pjax js-filter'));?></div></th>
						<th class="js-filter js-no-pjax text-left"><?php echo sprintf(__l('%s Status'), Configure::read('project.alt_name_for_reward_singular_caps'));?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody class="h5">
					<?php
					if (!empty($projectFunds)):
					$pledge_amount = $site_fee_amount = $paid_amount = 0;
					foreach ($projectFunds as $projectFund):
					$pledge_amount += $projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee'];
					$site_fee_amount += $projectFund['ProjectFund']['site_fee'];
					$paid_amount += $projectFund['ProjectFund']['amount'];
					?>
					<?php if(!empty($projectFund['Project']['Pledge'])){ ?>
					<tr>
						<td class="text-center">
						<?php if ($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding && ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Authorized)): ?>
							<div class="dropdown">
								<a href="#" title="Actions" data-toggle="dropdown" data-hover="dropdown" class="dropdown-toggle js-no-pjax"><i class="fa fa-cog"></i><span class="hide">Action</span></a>
								<ul class="dropdown-menu">
									<li>
									<?php  echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.sprintf(__l('Cancel %s'),Configure::read('project.alt_name_for_pledge_singular_caps')), array('controller' => 'project_funds', 'action' => 'edit_fund', 'project_fund' => $projectFund['ProjectFund']['id'], 'type' => 'cancel', 'return_page' => 'admin', 'admin' => false), array('class' => 'js-confirm','escape'=>false, 'title' => sprintf(__l('Cancel %s'),Configure::read('project.alt_name_for_pledge_singular_caps')))); ?>
									</li>
									<?php echo $this->Layout->adminRowActions($projectFund['ProjectFund']['id']);  ?>
								</ul>
							</div>
						<?php endif; ?>
						</td>
						<?php if(empty($this->request->params['named']['view_type'])) : ?>
						<td class="text-left">
						<div class="clearfix htruncate">
						<?php
						if($is_wallet_enabled)
						{
						$project_status = $projectFund['Project']['Pledge']['PledgeProjectStatus']['name'];
						}
						else
						{
						$project_status = str_replace("Refunded","Voided",$projectFund['Project']['Pledge']['PledgeProjectStatus']['name']);
						}
						?>
						<i title="<?php echo $this->Html->cText($project_status, false);?>" class="fa fa-square project-status-<?php echo $this->Html->cInt($projectFund['Project']['Pledge']['pledge_project_status_id'], false);?>"></i>
						<?php echo $this->Html->link($this->Html->cText($projectFund['Project']['name']), array('controller'=> 'projects', 'action'=>'view', $projectFund['Project']['slug'],'admin' => false), array('escape' => false,'title'=>$this->Html->cText($projectFund['Project']['name'],false)));?>
						</div>
						</td>
						<?php endif; ?>
						<td class="text-left">
							<div class="media">
								<div class="pull-left">
									<?php echo $this->Html->getUserAvatar($projectFund['User'], 'micro_thumb',true, '', 'admin');?>
								</div>
								<div class="media-body">
									<p>
										<?php echo $this->Html->getUserLink($projectFund['User']); ?>
									</p>
								</div>
							</div>
						</td>
						<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount']);?></td>

						<td class="text-center">
						<?php echo $this->Html->cCurrency($projectFund['ProjectFund']['amount'] - $projectFund['ProjectFund']['site_fee']); ?>

						</td>
						<td class="text-center"><?php echo $this->Html->cCurrency($projectFund['ProjectFund']['site_fee']);?></td>


						<td class="text-center"><?php echo $this->Html->cDateTimeHighlight($projectFund['ProjectFund']['created']);?></td>

						<td>
						<?php
						$refund = __l('Funded');
						if($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding) {
						$refund = Configure::read('project.alt_name_for_pledge_past_tense_caps');
						} else {
						$refund = __l('Funded');
						}
						if ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
						$refund = __l('Failed');
						$class = ' class="hide js-faild"';
						} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::PaymentFailed) {
						$refund = __l('Canceled');
						$class = ' class="hide js-faild"';
						} elseif ($projectFund['ProjectFund']['project_fund_status_id'] == ConstProjectFundStatus::Canceled) {
						$refund = 'Refunded';
						$refund = $refund;
						}
						echo $refund;
						?>
						</td>
						<?php if (isPluginEnabled('ProjectRewards')) { ?>
						<td class="text-left">
						<?php
						if ($projectFund['ProjectFund']['project_reward_id'] == 0) :
						echo __l('n/a');
						?>
						<?php else: ?>
						<div class="js-tooltip" title="<?php echo Configure::read('site.currency') . $this->Html->cText($projectFund['ProjectReward']['pledge_amount'], false).' + '.$this->Html->cText($projectFund['ProjectReward']['reward'], false); ?>">
						<?php echo Configure::read('site.currency').$this->Html->cText($projectFund['ProjectReward']['pledge_amount']).' + '.$this->Html->cText($projectFund['ProjectReward']['reward']); ?>
						</div>
						<?php endif; ?>
						<?php if(!empty($projectFund['ProjectReward']['estimated_delivery_date']) && !empty($projectFund['ProjectReward']['is_shipping']) && $projectFund['ProjectReward']['is_shipping']) : ?>
						<span><strong><?php echo __l('Estimated Delivery Date: ').$this->Html->cDate($projectFund['ProjectReward']['estimated_delivery_date']); ?></strong></span>
						<?php endif; ?>
						</td>
						<td class="text-center">
						<?php
						$reward = '';
						if (!empty($projectFund['ProjectFund']['project_reward_id']) && ($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed || $projectFund['Project']['Pledge']['pledge_project_status_id']== ConstPledgeProjectStatus::GoalReached) && empty($projectFund['ProjectFund']['is_given'])):
						$reward = __l('Not received');?>
						<?php endif; ?>
						<?php if (!empty($projectFund['ProjectFund']['is_given'])):
						$reward = __l('Received');?>
						<?php endif; ?>
						<?php if(empty($reward)) {
						$reward = __l('n/a');
						}
						?>
						<p><span><?php echo $reward; ?></span></p>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
					<?php
					endforeach;
					?>
					<?php
					else:
					?>
					<tr>
						<td colspan="9" class="text-center text-danger"><i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo sprintf(__l('No %s Funds available'), Configure::read('project.alt_name_for_project_singular_caps'));?></td>
					</tr>
					<?php
					endif;
					?>
				</tbody>
			</table>
		</div>			
	</div>
	<div class="page-sec navbar-btn">
		<?php
		if (!empty($projectFunds)) : ?>
		<div class="row">
			<div class="col-xs-12 col-sm-6 pull-right">
				<?php  echo $this->element('paging_links'); ?>
			</div>
		</div>
		<?php endif;?>
	</div>
</div>