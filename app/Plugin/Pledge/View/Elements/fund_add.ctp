<?php
$class = '';
if(!empty($response_data['pledge'])){
$pledge = $response_data['pledge'];
  if (strlen($project['Project']['name']) > 40) {
  $class .= ' title-double-line';
  }
}
$strAdditionalInfo = '';
?>
<div>
<section data-offset-top="10" data-spy="" class="<?php echo $class; ?>">
  <div class="row">
  <div class="payment-img col-sm-1"> <?php echo $this->Html->link($this->Html->showImage('Project', $project['Attachment'], array('dimension' => 'medium_thumb', 'alt' => sprintf(__l('[Image: %s]'), $this->Html->cText($project['Project']['name'], false)), 'title' => $this->Html->cText($project['Project']['name'], false), 'class' => 'js-tooltip'),array('aspect_ratio'=>1)), array('controller' => 'projects', 'action' => 'view',  $project['Project']['slug'], 'admin' => false), array('escape' => false)); ?> </div>
  <div class="col-sm-10 pull-left">
  <h3 class="list-group-item-heading"><?php echo $this->Html->link($this->Html->filterSuspiciousWords($this->Html->cText($project['Project']['name'], false), $project['Project']['detected_suspicious_words']), array('controller' => 'projects', 'action' => 'view', $project['Project']['slug']), array('escape' => false));?></h3>
  <p> <?php echo __l('A') . ' '; ?>
    <?php
    $response = Cms::dispatchEvent('View.Project.displaycategory', $this, array(
    'data' => $project
    ));
    if (!empty($response->data['content'])) {
    echo $response->data['content'];
    }
  ?>
    <?php echo sprintf(__l('%s in '), Configure::read('project.alt_name_for_project_singular_small')) . ' '; ?>
    <?php
    if (!empty($project['City']['name'])) {
    echo $this->Html->cText($project['City']['name'], false) . ', ';
    }
    if (!empty($project['Country']['name'])) {
    echo $this->Html->cText($project['Country']['name'], false);
    }
  ?>
    <?php echo __l(' by '); ?><?php echo $this->Html->link($this->Html->cText($project['User']['username']), array('controller' => 'users', 'action' => 'view', $project['User']['username']), array('escape' => false));?>

  </p>
  </div>
  </div>
  </section>
</div>
<div class="projectFunds clearfix">
  <div>
  <div class="alert alert-info">
  <?php
    if($project['Project']['payment_method_id'] == ConstPaymentMethod::AoN) {
    if ($project['Project']['collected_amount'] < $project['Project']['needed_amount']):
      echo sprintf(__l('We will authorize the %s amount. This amount will be captured only when the %s reaches the goal and end date.'), Configure::read('project.alt_name_for_pledge_singular_small'), Configure::read('project.alt_name_for_project_singular_small'));
    else:
      echo sprintf(__l('We will authorize the %s amount. This amount will be captured once %s reached end date.'), Configure::read('project.alt_name_for_pledge_singular_small'), Configure::read('project.alt_name_for_project_singular_small'));
    endif;
    } elseif($project['Project']['payment_method_id'] == ConstPaymentMethod::KiA) {
    echo sprintf(__l('We will authorize the %s amount. This amount will be captured once %s reached end date.'), Configure::read('project.alt_name_for_pledge_singular_small'), Configure::read('project.alt_name_for_project_singular_small'));
    }
  ?>
  </div>
  </div>
<div class="clearfix">
  <div>
  <?php
	if(isset($this->request->data['ProjectFund']['wallet']) && $this->request->data['ProjectFund']['payment_gateway_id'] == ConstPaymentGateways::SudoPay && !empty($sudopay_gateway_settings) && $sudopay_gateway_settings['is_payment_via_api'] == ConstBrandType::VisibleBranding) {
		echo $this->element('sudopay_button', array('data' => $sudopay_data, 'cache' => array('config' => 'sec')), array('plugin' => 'Sudopay'));
	?>
	</div>
	<?php
	} else {
	?>
  <div class="clearfix">
  <fieldset>
  <legend><?php echo sprintf(__l('%s Amount'),Configure::read('project.alt_name_for_pledge_singular_caps')); ?></legend>
  <div>
  <?php
        echo $this->Form->input('latitude',array('type' => 'hidden', 'id'=>'latitude'));
        echo $this->Form->input('longitude',array('type' => 'hidden', 'id'=>'longitude'));
        echo $this->Form->input('project_id',array('type'=>'hidden'));
        if (!empty($this->request->params['named']['project_reward_id'])):
          echo $this->Form->input('project_reward_id',array('type'=>'hidden', 'value'=>$this->request->params['named']['project_reward_id']));
		elseif (!empty($project['ProjectReward'])):
			echo $this->Form->input('project_reward_id',array('type'=>'hidden', 'value'=>-1));
		endif;
        if (!empty($pledge['Pledge']['pledge_type_id'])&&($pledge['Pledge']['pledge_type_id'] == ConstPledgeTypes::Reward)) {
          echo $this->Form->input('amount',array('type' =>'hidden'));
        }
        if (!empty($pledge['Pledge']['pledge_type_id'])&&($pledge['Pledge']['pledge_type_id'] == ConstPledgeTypes::Fixed )) {
          echo $this->Form->input('amount',array('readonly'=>true,'label' => sprintf(__l('%s amount'),Configure::read('project.alt_name_for_pledge_singular_caps')) .' ('.Configure::read('site.currency').')'));
        } else {
          echo $this->Form->input('amount',array('label' => sprintf(__l('%s amount'),Configure::read('project.alt_name_for_pledge_singular_caps')) .' ('.Configure::read('site.currency').')'));
        }?>
   </div>
  </fieldset>
  <?php if (!empty($project['ProjectReward']) && isPluginEnabled('ProjectRewards')): ?>
  <fieldset>
  <legend><?php echo sprintf(__l('Select your %s'),Configure::read('project.alt_name_for_reward_singular_small')); ?></legend>
  <div class="group-block">
  <?php
          if (!empty($pledge['Pledge']['pledge_type_id'])&&($pledge['Pledge']['pledge_type_id'] != ConstPledgeTypes::Reward)) {
          $options = array(0 => '<span>' . sprintf(__l('No %s'), Configure::read('project.alt_name_for_reward_singular_caps')) . '</span>');
          echo $this->Form->input('project_reward', array('type' => 'radio', 'checked'=> 'checked', 'options' => $options, 'after' => '<span class="help">' . sprintf(__l('No thanks, I just want to help the %s.'), Configure::read('project.alt_name_for_project_singular_small')) .'</span>','class' => 'js-reward-input js-no-pjax {reward:-1}'));
          }
          foreach ($project['ProjectReward'] as $reward) :
          $disabled = false;
           if ($reward['pledge_max_user_limit'] > 0 and $reward['pledge_max_user_limit'] <= $reward['project_fund_count']) :
            $disabled = 'disabled';
          elseif($reward['pledge_amount'] > (!empty($project['Project']['needed_amount']) && $project['Project']['needed_amount'] - $project['Project']['collected_amount']) && !empty($pledge['Pledge']['is_allow_over_funding'])):
          $disabled = 'disabled';
          endif;
        ?>
  <div class="rewards">
    <?php if ($disabled) :?>
    <div class="input radio">
    <div class="round-3 sold-out <?php echo $disabled;?>"><input type="radio" disabled="true" /><span class="label label-inverse"><?php echo __l('Sold out');?></span>
    <span><?php echo $this->Html->siteCurrencyFormat($this->Html->cCurrency($reward['pledge_amount'], false)).' + '; ?></span> <span class="help"><?php echo $this->Html->cText($reward['reward'], false); ?><span><?php if(isset($reward['is_shipping']) && $reward['is_shipping']) { echo ' ('.__l('Estimated delivery date') . ': ' . $this->Html->cDate($reward['estimated_delivery_date']).')'; } ?></span></span></div></div>
    <?php else:?>
    <?php
    $strDeliveryDate = '';
    if(isset($reward['is_shipping']) && $reward['is_shipping']) {
    $strDeliveryDate = '('.__l('Estimated delivery date').': '.$this->Html->cDate($reward['estimated_delivery_date']).')';
    }
            echo $this->Form->input('project_reward', array('type' => 'radio','id'=>'reward_'.$reward['id'],'options' => array($reward['id'] => $this->Html->siteCurrencyFormat($this->Html->cCurrency($reward['pledge_amount'], false)).' +'), 'after' => '<span class="help">'.$this->Html->cText($reward['reward'], false).' <span>'.$strDeliveryDate.'</span></span>', 'disabled' => $disabled, 'class' => 'js-reward-input js-no-pjax js-reward-radio {reward: '.$reward['id'].',amount: '.$this->Html->cInt($reward['pledge_amount'],false).',is_shipping: '.(($reward['is_shipping'])?$reward['is_shipping']:0 ).',is_having_additional_info: '.(($reward['is_having_additional_info'])?$reward['is_having_additional_info']:0 ).',additional_info_label: "'.(($reward['additional_info_label'])?$reward['additional_info_label']:0 ).'"} '.$disabled));?>
    <?php endif; ?>
  </div>

  <?php
    if (!empty($reward['additional_info_label'])) {
    $strAdditionalInfo .= "<div class ='js-additional-infohide'>".$this->Form->input('ProjectFund.additional_info',array('label'=>$this->Html->cText($reward['additional_info_label'], false),'class'=>'js-additional-info-input'))."</div>";
    }
  ?>
  <?php endforeach; ?>
  </div>
  </fieldset>
  <?php
	if(!empty($strAdditionalInfo)) {
  ?>
  <div class='js-additional-infohide col-sm-9'>
	<?php
		echo $this->Form->input('ProjectFund.additional_info', array('label'=>  __l('Additional Info'), 'class'=>'js-additional-info-input'));
	?>
  </div>
  <?php } ?>
  <div class ='js-shipping-info hide col-md-9'>
  <div class="profile-block clearfix">
    <div class="mapblock-info mapblock-info1">
    <div class="clearfix address-input-block required">
    <?php  echo $this->Form->input('ProjectFund.address', array('label' => __l('Shipping Address'), 'class'=> 'js-preview-address-change','id' => 'ProjectAddressSearch', 'info' => __l('You must select address from autocomplete'))); ?>
    </div>
    <?php
          $class = '';
          if (empty($this->request->data['UserProfile']['address']) || ( !empty($this->request->data['UserProfile']['address1']) && !empty($this->request->data['City']['name']) &&  !empty($this->request->data['UserProfile']['country_id']))) {
            $class = 'hide';
          }
          ?>
    <div id="js-geo-fail-address-fill-block" class="<?php echo $class;?>">
    <div class="clearfix">
    <div class="map-address-left-block address-input-block">
      <?php
              echo $this->Form->input('ProjectFund.latitude', array('id' => 'latitude', 'type' => 'hidden'));
              echo $this->Form->input('ProjectFund.longitude', array('id' => 'longitude', 'type' => 'hidden'));
              echo $this->Form->input('ProjectFund.address1', array('id' => 'js-street_id','type' => 'text', 'label' => __l('Address')));
              echo $this->Form->input('ProjectFund.country_id',array('id'=>'js-country_id', 'empty' => __l('Please Select')));
              echo $this->Form->input('State.name', array('type' => 'text', 'label' => __l('State')));
              echo $this->Form->input('City.name', array('type' => 'text', 'label' => __l('City')));
              echo $this->Form->input('ProjectFund.zip_code',array('id'=>'UserProfileZipCode'));
            ?>
    </div>
    </div>
    </div>
    <div class="pull-right js-side-map-div col-md-3 <?php echo $class;?>">
    <h5><?php echo __l('Point Your Location');?></h5>
    <div class="js-side-map">
    <div id="js-map-container"></div>
    <span><?php echo __l('Point the exact location in map by dragging marker');?></span> </div>
    </div>
    <div id="mapblock">
    <div id="mapframe">
    <div id="mapwindow"></div>
    </div>
    </div>
    </div>
  </div>
  </div>
  <?php endif; ?>
  </div>
  <div class="clearfix">
  <div>
    <fieldset>
    <legend><?php echo sprintf(__l('Personalize your  %s'),Configure::read('project.alt_name_for_pledge_singular_caps')); ?></legend>
    <div class="group-block personal-radio"> <?php echo $this->Form->input('is_anonymous',array('type' =>'radio','options'=>$radio_options,'default'=>ConstAnonymous::None,'legend'=>false));?> </div>
    </fieldset>
  </div>  
  <div>
  <div class="clearfix">
  <?php echo $this->element('pledge-faq', array('cache' => array('config' => 'sec')),array('plugin' => 'Pledge')); ?>
  </div>
  </div>
  </div></div></div></div>
 	
 	 <div class="clearfix">
    <legend><?php echo __l('Select Payment Type'); ?></legend>
    <?php
    echo $this->element('payment-get_gateways', array('model'=>'ProjectFund','type'=>'is_enable_for_pledge','is_enable_wallet'=>1,'project_type'=>$project['ProjectType']['name'],'user_id'=>$project['Project']['user_id'], 'cache' => array('config' => 'sec')));?>
    
  </div>
<?php } ?>
  

