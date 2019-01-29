<?php /* SVN: $Id: index.ctp 2901 2010-09-02 11:49:34Z sakthivel_135at10 $ */ ?>
<?php if (!$this->request->params['isAjax']) { ?>
  <div class="js-response pledge space hor-mspace">
<?php } ?>
<div class="clearfix space" id="js-pledge-scroll">
	<div class="pledge-status text-info text-b" itemprop="Name"> <span class="ver-space"><?php echo $this->Html->image('pledge-hand.png', array('class'=>'right-mspace-xs','width' => 50, 'height' => 50)); ?></span><span class="no-mar h3"><?php echo Configure::read('project.alt_name_for_pledge_singular_caps'); ?></span> </div>	
</div>
  <div class="clearfix hor-space">
    <ul class="filter-list-block list-inline">
      <li><?php echo $this->Html->link('<span class="badge badge-info"><strong>'.$this->Html->cInt($count,false).'</strong></span><span class="show">' .__l('All'). '</span>', array('controller'=>'pledges','action'=>'myprojects', 'status' => 'all'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		<?php if(Configure::read('Project.is_project_owner_select_funding_method')): ?>
		  <li class="text-center"><?php echo $this->Html->link('<span class="badge badge-success"><span><strong>'.$this->Html->cInt($total_flexible_projects,false).'</strong></span></span><span class="show">' .__l('Flexible'). '</span>', array('controller'=>'pledges','action'=>'myprojects', 'status' => 'flexible'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		  <li class="text-center"><?php echo $this->Html->link('<span class="badge badge-blue"><span><strong>'.$this->Html->cInt($total_fixed_projects,false).'</strong></span></span><span class="show">' .__l('Fixed'). '</span>', array('controller'=>'pledges','action'=>'myprojects', 'status' => 'fixed'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
		  <?php endif; ?>
		  <?php	
		  $approvedCountInfo = count($formFieldSteps) > 1 ?' / '.__l('Admin Approved').' ('.$this->Html->cInt($approvedCount,false).')':'';
		  $countInfo = !empty($formFieldSteps)?'<i class="fa fa-info-circle sfont js-tooltip" title="'.__l('Admin Rejected').' ('.$this->Html->cInt($rejectedCount,false).')'.$approvedCountInfo.'"></i>':'';
		  ?>
      <li><?php echo $this->Html->link('<span class="badge badge-warning"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::Pending],false).$countInfo.'</strong></span></span><span class="show">' .__l('Pending'). '</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'pending'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?>
	  </li>
      <?php if(isPluginEnabled('Idea')): ?>
        <li><?php echo $this->Html->link('<span class="badge badge-green"><span><strong>' . $this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::OpenForIdea], false) . '</strong></span></span><span class="show">' .__l('Open for Voting'). '</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'idea'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <?php endif; ?>
      <li><?php echo $this->Html->link('<span class="badge badge-primary"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::OpenForFunding],false).'</strong></span></span><span class="show">' .__l('Open for Funding'). '</span>', array('controller'=>'pledges','action'=>'myprojects'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-default"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::GoalReached],false).'</strong></span></span><span class="show">' .__l('Goal Reached'). '</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'goal'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-darkgreen"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::FundingClosed],false).'</strong></span></span><span class="show">' .sprintf(__l('Funding Closed and Paid to %s'),Configure::read('project.alt_name_for_pledge_project_owner_singular_caps')). '</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'closed'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <?php
        if ($is_wallet_enabled) {
          $current_title = 'Refunded';
        } else {
          $current_title = 'Voided';
        }
      ?>
      <li><?php echo $this->Html->link('<span class="badge badge-danger"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::ProjectCanceled],false).'</strong></span></span><span class="show">' .__l($current_title. " due to Canceled").'</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'cancelled'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-lightblue"><span><strong>'.$this->Html->cInt($projectStatuses[ConstPledgeProjectStatus::FundingExpired],false).'</strong></span></span><span class="show">' .__l($current_title. " due to Expired").'</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'expired'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
      <li><?php echo $this->Html->link('<span class="badge badge-black"><span><strong>'.$this->Html->cInt($system_drafted,false).'</strong></span></span><span class="show">' .__l('Drafted'). '</span>', array('controller'=>'pledges','action'=>'myprojects','status'=>'draft'), array('class' => 'js-filter js-no-pjax', 'escape' => false));?></li>
    </ul>
  </div>
  <?php  echo $this->element('paging_counter'); ?>
  <div class="table-responsive">
  <table class="table table-striped table-bordered table-condensed table-hover panel">
    <tr>
      <?php if (empty($this->request->params['named']['status']) || !in_array($this->request->params['named']['status'], array('goal', 'cancelled', 'expired'))) { ?>
        <th class="text-center table-action-width"><?php echo __l('Actions');?></th>
      <?php } ?>
      <th class="text-left"><div class="js-filter"><?php echo $this->Paginator->sort('Project.name', __l('Name') ,array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.collected_amount', __l('Collected Amount') ,array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax')).' ('.Configure::read('site.currency').')';?></div> / <div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.needed_amount', __l('Needed'),array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php if( !empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')): ?>
        <th  class="text-right"><div class="js-filter text-left"><?php echo $this->Paginator->sort('Project.commission_amount', __l('Received amount') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax')).' ('.Configure::read('site.currency').')';?></div></th>
      <?php endif; ?>
      <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort( 'Project.project_fund_count', Configure::read('project.alt_name_for_backer_plural_caps') ,array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php
        if (!empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')):
          $colspan = 3;
        else:
          $colspan = 2;
        endif;
      ?>
      <th class="text-center"><div><?php echo __l('Funding Date'); ?>
        <div class="js-filter"><?php echo $this->Paginator->sort('Project.project_start_date', __l('Start') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div> / <div class="js-filter js-no-pjax"><?php echo $this->Paginator->sort('Project.project_end_date', __l('End') ,array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></div>
      </th>
	  <?php if (Configure::read('Project.is_project_owner_select_funding_method')) : ?>
		<th class="text-center">
        <div><span class="clearfix"><?php echo __l('Fixed Funding'); ?></span><i class="fa fa-info-circle js-tooltip" data-placement="top" title="<?php echo sprintf(__l('Fixed funding:  %s fund will be captured only if it reached the needed amount.When %s has been reached the ending date, then funds can start to be released.'), Configure::read('project.alt_name_for_project_singular_caps'), Configure::read('project.alt_name_for_project_singular_small')); echo "\n";echo sprintf(__l('Flexible funding:  %s fund will be captured even if it does not reached the needed amount.'), Configure::read('project.alt_name_for_project_singular_caps')); ?>"></i></div>
        </th>
		<?php endif; ?>
	  <?php if(isPluginEnabled('ProjectUpdates')): ?>
      <th class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.blog_count', __l('Updates') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
	  <?php endif; ?>
      <?php if(isPluginEnabled('ProjectFollowers')): ?>
        <th  class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.project_follower_count', __l('Followers') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php endif; ?>
      <th class="text-center"><div class="js-filter text-center"><?php echo $this->Paginator->sort('Project.message_count', __l('Comments') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
        <th><div class="js-filter"><?php echo $this->Paginator->sort('Pledge.project_fund_goal_reached_date', __l('Goal Reached Date') , array('url'=>array('controller'=>'pledges','action'=>'myprojects'), 'class' => 'js-no-pjax'));?></div></th>
      <?php endif; ?>
      <?php if(isPluginEnabled('ProjectRewards')) : ?>
      <th class="text-center"><div class="text-center"><?php echo __l('Reward details'); ?></div></th>
      <?php endif; ?>
    </tr>
    <?php
      if (!empty($projects)):
        $i = 0;
        foreach ($projects as $project):
          if(!empty($project['Project']['project_end_date'])):
            $time_strap= strtotime($project['Project']['project_end_date']) -strtotime( date('Y-m-d'));
            $days = floor($time_strap /(60*60*24));
            if ($days > 0) {
              $project[0]['enddate'] =$days;
            } else {
              $project[0]['enddate'] =0;
            }
          endif;
    ?>
    <tr>
      <?php if (empty($this->request->params['named']['status']) || !in_array($this->request->params['named']['status'], array('goal', 'cancelled', 'expired'))) { ?>
        <td class="text-center">
          <?php if ((!empty($project['Project']['ProjectReward']) && $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed) || (!in_array($project['Pledge']['pledge_project_status_id'], array(ConstPledgeProjectStatus::GoalReached, ConstPledgeProjectStatus::ProjectCanceled, ConstPledgeProjectStatus::FundingExpired, ConstPledgeProjectStatus::FundingClosed)))): ?>
            <div class="dropdown">
              <a href="#" title="Actions" data-toggle="dropdown" class="fa fa-cog fa-lg dropdown-toggle js-no-pjax"><span class="hide">Action</span></a>
              <ul class="list-unstyled dropdown-menu text-left clearfix">
                <?php if($project['Project']['is_draft']||$project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::Pending || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForIdea || ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding && Configure::read('Project.is_allow_project_owner_to_edit_project_in_open_status'))): ?>
                  <li><?php echo $this->Html->link('<i class="fa fa-pencil-square-o"></i>'.__l('Edit'), array('controller' => 'projects', 'action' => 'edit', $project['Project']['id']), array('class' => 'edit js-edit', 'title' => __l('Edit'),'escape'=>false)); ?></li>
		  <?php if($project['Project']['is_draft']){ ?>
		  <li><?php echo $this->Html->link('<i class="fa fa-cog"></i>'.__l('Mark as active'), array('controller' => 'projects', 'action' => 'update_status', $project['Project']['id']), array('class' => 'edit js-edit', 'title' => __l('Mark as active'),'escape'=>false)); ?></li>
		  <?php } ?>
		  <?php if($project['Project']['is_draft'] || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::Pending){ ?>
		  <li><?php echo $this->Html->link('<i class="fa fa-times fa-fw"></i>'.__l('Delete'), Router::url(array('controller'=>'projects','action' => 'delete', $project['Project']['id']),true).'?redirect_to='.$this->request->url,array('class' => 'js-confirm', 'escape'=>false,'title' => __l('Delete')));?></li>
		  <?php } ?>
		<?php endif; ?>
                <?php if (Configure::read('Project.is_allow_owner_project_cancel') and $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding) : ?>
                  <li><?php echo $this->Html->link('<i class="fa fa-times"></i>'.__l('Cancel'), array('controller' => 'projects', 'action' => 'cancel', $project['Project']['id']), array('class' => 'edit js-confirm cancel', 'title' => __l('Cancel'), 'escape'=>false)); ?></li>
                <?php endif; ?>
                <?php  if (!empty($project['Project']['ProjectReward']) && $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::FundingClosed): ?>
                  <li><?php echo $this->Html->link('<i class="fa fa-gift fa-fw"></i>'. sprintf(__l('Manage %s'), Configure::read('project.alt_name_for_reward_plural_caps')), array('controller'=>'project_funds','action'=>'index', 'project_id'=>$project['Project']['id'],'type'=>'manage', 'modal' => 'modal'), array('data-toggle' => 'modal', 'data-target' => '#js-ajax-modal','class'=>'js-no-pjax','id'=>'', 'escape' => false, 'title' => sprintf(__l('Manage %s'), Configure::read('project.alt_name_for_reward_plural_caps')))); ?></li>
                <?php endif; ?>
                <?php if (in_array($project['Pledge']['pledge_project_status_id'], array(ConstPledgeProjectStatus::OpenForIdea, ConstPledgeProjectStatus::GoalReached, ConstPledgeProjectStatus::OpenForFunding)) && isPluginEnabled('SocialMarketing')) { ?>
                  <li><?php  echo $this->Html->link('<i class="fa fa-share"></i>'.__l('Share'), array('controller'=>'social_marketings','action'=>'publish', $project['Project']['id'],'type'=>'facebook', 'publish_action' => 'add'), array( 'title' => __l('Share'),'escape'=>false)); ?></li>
                <?php } ?>
              </ul>
            </div>
          <?php endif; ?>
        </td>
      <?php } ?>
      <td class="text-left">
        <?php
          if ($is_wallet_enabled) {
            $project_status = $project['PledgeProjectStatus']['name'];
          } else {
            $project_status = str_replace("Refunded","Voided",$project['PledgeProjectStatus']['name']);
          }
        ?>
        <i title="<?php echo $this->Html->cText($project['PledgeProjectStatus']['name'], false);?>" class="fa fa-square fa-fw fa-lg project-status-<?php echo $this->Html->cInt($project['Pledge']['pledge_project_status_id'], false);?>"></i>
		<?php if(!empty($formFieldSteps) && in_array($project['Project']['id'], $rejectedProjectIds)):?>
			<i class="fa fa-info-circle sfont js-tooltip" title="<?php echo __l('Admin Rejected'); ?>"></i>
		<?php endif; ?>
		<?php if(!empty($formFieldSteps) && count($formFieldSteps) > 1 && in_array($project['Project']['id'], $approvedProjectIds)):?>
			<i class="fa fa-info-circle sfont js-tooltip greenc" title="<?php echo __l('Admin Approved'); ?>"></i>
		<?php endif; ?>
		<?php echo $this->Html->link($this->Html->cText($project['Project']['name'],false) , array('controller'=>'projects' , 'action'=>'view' , $project['Project']['slug'] , 'admin'=>false) , array('class' => 'cboxelement', 'escape' => false,'title'=> $this->Html->cText($project['Project']['name'],false)));?>
		<?php if ($project['Project']['payment_method_id'] == ConstPaymentMethod::KiA && Configure::read('Project.is_project_owner_select_funding_method')):
			echo '<div class="clearfix"><span class="label label-info pro-status-11">'.__l('Flexible').'</span></div>';
		  endif;
		?>

      </td>
      <td class="text-right">
        <?php $collected_percentage = ($project['Project']['collected_percentage']) ? $project['Project']['collected_percentage'] : 0; ?>
        <div class="progress">
          <div style="width:<?php echo ($collected_percentage > 100) ? '100%' : $collected_percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($collected_percentage, false).'%'; ?>" class="progress-bar progress-bar-info"></div>
        </div>
        <p class="text-center no-mar"><?php echo $this->Html->cCurrency($project['Project']['collected_amount']); ?> / <?php echo $this->Html->cCurrency($project['Project']['needed_amount']); ?></p>
      </td>
      <?php if( !empty($this->request->params['named']['status']) && ($this->request->params['named']['status'] == 'goal')): ?>
        <td class="text-right"><?php echo $this->Html->cCurrency($project['Project']['collected_amount'] - $project['Project']['commission_amount']); ?></td>
      <?php endif; ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_fund_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#backers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_fund_count'], false))); ?></td>
      <td class="text-center">
        <?php
          if (empty($project['Project']['project_start_date']) || $project['Project']['project_start_date'] == '0000-00-00')   {
            echo '-';
          } else {
        ?>
        <div class="clearfix">
          <div class="progress-block clearfix">
            <?php
              $project_progress_precentage = 0;
              if(strtotime($project['Project']['project_start_date']) < strtotime(date('Y-m-d H:i:s'))) {
                if($project['Project']['project_end_date'] !==   NULL) {
                  $days_till_now = (strtotime(date("Y-m-d")) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  $total_days = (strtotime(date($project['Project']['project_end_date'])) - strtotime(date($project['Project']['project_start_date']))) / (60 * 60 * 24);
                  if($total_days) {
                    $project_progress_precentage = round((($days_till_now/$total_days) * 100));
                  } else {
                    $project_progress_precentage = round((($days_till_now) * 100));
                  }
                  if($project_progress_precentage > 100) {
                    $project_progress_precentage = 100;
                  }
                } else {
                  $project_progress_precentage = 100;
                }
              }
            ?>
            <?php if ($project['Project']['project_end_date']): ?>
              <div class="progress">
                <div style="width:<?php echo ($project_progress_precentage > 100) ? '100%' : $project_progress_precentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($project_progress_precentage, false).'%'; ?>" class="progress-bar"></div>
              </div>
            <?php endif; ?>
            <p class="progress-value clearfix"><span><?php echo $this->Html->cDateTimeHighlight($project['Project']['project_start_date']);?></span>&nbsp;/&nbsp;<span><?php echo (!is_null($project['Project']['project_end_date']))? $this->Html->cDateTimeHighlight($project['Project']['project_end_date']): ' - ';?></span></p>
          </div>
        </div>
        <?php } ?>
      </td>
	  <?php if (Configure::read('Project.is_project_owner_select_funding_method')) : ?>
	  <td class="text-center"><?php if($project['Project']['payment_method_id']==ConstPaymentMethod::AoN){ echo 'Yes'; } else { echo 'No'; }?></td>
	  <?php endif; ?>
      <?php if(Configure::read('Project.is_project_comment_enabled') && !Configure::read('Project.is_fb_project_comment_enabled')):  ?>
        <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_comment_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#comments', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_comment_count'], false))); ?></td>
      <?php endif; ?>
	  <?php if(isPluginEnabled('ProjectUpdates')): ?>
      <td class="text-center">
        <?php
          if (!empty($project['Project']['feed_url'])) {
            echo $this->Html->link($this->Html->cInt($project['Project']['project_feed_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title'=> $this->Html->cInt($project['Project']['project_feed_count'], false)));
          } else {
            echo $this->Html->link($this->Html->cInt($project['Project']['blog_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#updates', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['blog_count'], false)));
          }
        ?>
      </td>
	  <?php endif; ?>
      <?php if(isPluginEnabled('ProjectFollowers')): ?>
        <td class="text-center"><?php echo $this->Html->link($this->Html->cInt($project['Project']['project_follower_count'], false), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], '#followers', 'admin' => false), array('class' => 'cboxelement', 'escape' => false, 'title' => $this->Html->cInt($project['Project']['project_follower_count'], false)));?></td>
      <?php endif; ?>
      <td class="text-center"><?php echo $this->Html->link($this->Html->cInt(count($project['Project']['Message']), false),array('controller' => 'projects', 'action' => 'view', $project['Project']['slug'], 'admin' => false, '#comments'), array('escape' => false, 'title' => $this->Html->cInt(count($project['Project']['Message']), false)));?></td>
      <?php if( !empty($this->request->params['named']['status']) && $this->request->params['named']['status'] == 'goal'): ?>
        <td class="text-center"><?php echo ($project['Pledge']['project_fund_goal_reached_date'])?$this->Html->cDate($project['Pledge']['project_fund_goal_reached_date']):' ';?></td>
      <?php endif; ?>

      <?php if(isPluginEnabled('ProjectRewards')) : ?>
      <td class="text-center">
         <div class="progress-block clearfix">
          <?php
            if (empty($project['Project']['rewarded_count'])) {
              echo __l('n/a');
            } else {
              $percentage = ($project['Project']['reward_given_count'] / $project['Project']['rewarded_count']) * 100;
          ?>
              <div class="progress progress-bar-info">
                <div style="width:<?php echo ($percentage > 100) ? '100%' : $percentage.'%'; ?>;" title = "<?php echo $this->Html->cFloat($percentage, false).'%'; ?>" class="progress-bar"></div>
              </div>
              <p class="progress-value clearfix"><?php echo $this->Html->cInt($project['Project']['reward_given_count']); ?> / <?php echo $this->Html->cInt($project['Project']['rewarded_count']); ?></p>
           <?php
            }


          ?>
        </div>
      </td>
      <?php endif; ?>
    </tr>
    <?php
        endforeach;
      else:
    ?>
    <tr>
      <td colspan="22">
      <div class="text-center no-items">
		<p><?php echo sprintf(__l('No %s available'), Configure::read('project.alt_name_for_pledge_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps'));?></p>
	  </div>
	  </td>
    </tr>
    <?php
      endif;
    ?>
  </table>
  </div>
  <?php if (!empty($projects)) { ?>
    <div class="clearfix">
      <div class="pull-right paging js-pagination js-no-pjax {'scroll':'js-pledge-scroll'}"> <?php echo $this->element('paging_links'); ?> </div>
    </div>
  <?php } ?>
<?php if (!$this->request->params['isAjax']) { ?>
  </div>
<?php } ?>