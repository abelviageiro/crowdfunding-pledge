<?php
/**
 * CrowdFunding
 *
 * PHP version 5
 *
 * @category   PHP
 * @package    Crowdfunding
 * @subpackage Core
 * @author     Agriya <info@agriya.com>
 * @copyright  2018 Agriya Infoway Private Ltd
 * @license    http://www.agriya.com/ Agriya Infoway Licence
 * @link       http://www.agriya.com
 */
class PledgeEventHandler extends Object implements CakeEventListener
{
    /**
     * implementedEvents
     *
     * @return array
     */
    public function implementedEvents() 
    {
        return array(
            'View.Project.displaycategory' => array(
                'callable' => 'onCategorydisplay'
            ) ,
            'View.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Controller.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox'
            ) ,
            'Behavior.ProjectType.GetProjectStatus' => array(
                'callable' => 'onMessageInbox',
            ) ,
            'View.Project.onCategoryListing' => array(
                'callable' => 'onCategoryListingRender',
            ) ,
            'View.Project.projectStatusValue' => array(
                'callable' => 'getProjectStatusValue'
            ) ,
            'Model.Project.beforeAdd' => array(
                'callable' => 'onProjectValidation',
            ) ,
            'Controller.Projects.afterAdd' => array(
                'callable' => 'onProjectAdd',
            ) ,
            'Controller.Projects.afterEdit' => array(
                'callable' => 'onProjectEdit',
            ) ,
            'Controller.ProjectFunds.beforeAdd' => array(
                'callable' => 'isAllowAddFund',
            ) ,
            'Controller.ProjectFunds.beforeValidation' => array(
                'callable' => 'onProjectFundValidation',
            ) ,
            'Controller.ProjectFunds.afterAdd' => array(
                'callable' => 'onProjectFundAdd',
            ) ,
            'Controller.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Model.Project.openFunding' => array(
                'callable' => 'onOpenFunding',
            ) ,
            'Controller.ProjectType.projectIds' => array(
                'callable' => 'onMessageDisplay',
            ) ,
            'Controller.ProjectType.ClosedProjectIds' => array(
                'callable' => 'getClosedProjectIds',
            ) ,
            'Controller.ProjectType.getConditions' => array(
                'callable' => 'getConditions',
            ) ,
            'Controller.ProjectType.getContain' => array(
                'callable' => 'getContain',
            ) ,
            'Controller.ProjectType.getProjectTypeStatus' => array(
                'callable' => 'getProjectTypeStatus',
            ) ,
            'View.Project.howitworks' => array(
                'callable' => 'howitworks',
                'priority' => 1
            ) ,
            'View.AdminDasboard.onActionToBeTaken' => array(
                'callable' => 'onActionToBeTakenRender'
            ) ,
            'Controller.FeatureProject.getConditions' => array(
                'callable' => 'getFeatureProjectList'
            ) ,
        );
    }
    /**
     * onCategoryListing
     *
     * @param CakeEvent $event
     * @return void
     */
    public function onCategoryListingRender($event) 
    {
        $content = '';
        if (!empty($event->data['data']['project_type']) && $event->data['data']['project_type'] == 'pledge') {
            $view = $event->subject();
            App::import('Model', 'Pledge.PledgeProjectCategory');
            $this->PledgeProjectCategory = new PledgeProjectCategory();
            $projectCategories = $this->PledgeProjectCategory->find('all', array(
                'fields' => array(
                    'PledgeProjectCategory.name',
                    'PledgeProjectCategory.slug'
                ) ,
                'limit' => 10,
                'order' => 'PledgeProjectCategory.name asc'
            ));
            if (!empty($projectCategories)) {
                $content = '<h4>' . __l('Filter by Category') . '</h4>
        	     <ul class="nav navbar-nav nav-tabs nav-stacked">';
                foreach($projectCategories as $project_category) {
                    $class = (!empty($event->data['data']['category']) && $event->data['data']['category'] == $project_category['PledgeProjectCategory']['slug']) ? ' class="active"' : null;
                    $content.= '<li' . $class . '>' . $view->Html->link($project_category['PledgeProjectCategory']['name'], array(
                        'controller' => 'projects',
                        'action' => 'index',
                        'category' => $project_category['PledgeProjectCategory']['slug'],
                        'project_type' => 'pledge',
                    ) , array(
                        'title' => $project_category['PledgeProjectCategory']['name']
                    )) . '</li>';
                }
                $content.= '</ul>';
            }
        }
        $event->data['content'] = $content;
    }
    public function onProjectValidation($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $error = array();
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            App::import('Model', 'Pledge.Pledge');
            $this->Pledge = new Pledge();
            $this->Pledge->set($data);
            if (!$this->Pledge->validates()) {
                $error = $this->Pledge->validationErrors;
            }
        }
        $event->data['error']['Pledge'] = $error;
    }
    public function onProjectAdd($event) 
    {
        $controller = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            $pledge = $controller->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'Pledge.id',
                    'Pledge.pledge_project_status_id',
                ) ,
                'recursive' => 0
            ));
            if (!empty($pledge) && !empty($pledge['Pledge']['id'])) {
                $data['Pledge']['id'] = $pledge['Pledge']['id'];
            }
            if (empty($pledge['Pledge']['pledge_project_status_id'])) {
                if (!$data['Project']['is_draft']) {
                    $data['Pledge']['pledge_project_status_id'] = ConstPledgeProjectStatus::Pending;
                } else {
                    $data['Pledge']['pledge_project_status_id'] = 0;
                }
            }
            $data['Pledge']['project_id'] = $data['Project']['id'];
            $data['Pledge']['user_id'] = $controller->Auth->user('id');
            $controller->Project->Pledge->save($data);
        }
    }
    public function onProjectEdit($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        if ($data['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            App::import('Model', 'Pledge.Pledge');
            $this->Pledge = new Pledge();
            $pledge_data = $this->Pledge->find('first', array(
                'conditions' => array(
                    'Pledge.project_id' => $data['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (!empty($data['Project']['publish']) && empty($pledge_data['Pledge']['pledge_project_status_id'])) {
                $data['Pledge']['pledge_project_status_id'] = ConstPledgeProjectStatus::Pending;
            }
            $data['Pledge']['id'] = $pledge_data['Pledge']['id'];
            $this->Pledge->save($data);
        }
    }
    public function isAllowAddFund($event) 
    {
        $project = $event->data['data'];
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            App::import('Model', 'Pledge.Pledge');
            $this->Pledge = new Pledge();
            $pledge_data = $this->Pledge->find('first', array(
                'conditions' => array(
                    'Pledge.project_id' => $project['Project']['id']
                ) ,
                'recursive' => -1
            ));
            if (strtotime(date('Y-m-d 23:59:59', strtotime($project['Project']['project_end_date']))) > time() && !($pledge_data['Pledge']['is_allow_over_funding']) && $project['Project']['needed_amount'] <= $project['Project']['collected_amount']) {
                $event->data['error'] = sprintf(__l('%s has been not allowed overfunding') , Configure::read('project.alt_name_for_project_singular_caps'));
            } else {
                $event->data['pledge'] = $pledge_data;
            }
        }
    }
    public function onProjectFundValidation($event) 
    {
        $data = $event->data['data'];
        App::import('Model', 'Project.Project');
        $this->Project = new Project();
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $data['ProjectFund']['project_id']
            ) ,
            'contain' => array(
                'Pledge'
            ) ,
            'recursive' => 0
        ));
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            $validationErrors = '';
            if (($data['ProjectFund']['amount'] > $project['Project']['needed_amount']-$project['Project']['collected_amount']) && !($project['Pledge']['is_allow_over_funding'])) {
                $validationErrors['amount'] = __l('The amount should be less than needed amount.');
            } else if (!empty($project['Pledge']['pledge_type_id']) && !empty($project['Pledge']['min_amount_to_fund'])) {
                if ($project['Project']['needed_amount']%$data['ProjectFund']['amount'] != 0 && $project['Pledge']['pledge_type_id'] == ConstPledgeTypes::Multiple) {
                    $validationErrors['amount'] = __l('Amount should be multiple of ' . $project['Pledge']['min_amount_to_fund'] . ".");
                } else if ($project['Pledge']['min_amount_to_fund'] > $data['ProjectFund']['amount'] && ($project['Pledge']['pledge_type_id'] == ConstPledgeTypes::Minimum)) {
                    $validationErrors['amount'] = __l('The amount should not be less than ') . Configure::read('site.currency') . $project['Pledge']['min_amount_to_fund'];
                } else if ($project['Pledge']['min_amount_to_fund'] != $data['ProjectFund']['amount'] && ($project['Pledge']['pledge_type_id'] == ConstPledgeTypes::Fixed)) {
                    $validationErrors['amount'] = __l('The amount should be equal to ') . Configure::read('site.currency') . $project['Pledge']['min_amount_to_fund'];
                }
            }
            if (!empty($data['ProjectFund']['project_reward'])) {
                App::import('Model', 'Projects.ProjectReward');     
                $this->ProjectReward = new ProjectReward();
                $reward = $this->ProjectReward->find('first', array(
                    'conditions' => array(
                        'ProjectReward.id' => $data['ProjectFund']['project_reward']
                    )
                )); 
                if (!empty($reward) && $data['ProjectFund']['amount'] < $reward['ProjectReward']['pledge_amount']) {
                    $validationErrors['amount'] = sprintf(__l('You cannot select this %s for the amount you entered.') , Configure::read('project.alt_name_for_reward_singular_small'));
                }
                if (!empty($reward) && $reward['ProjectReward']['is_shipping']) {
                    App::import('Model', 'Pledge.PledgeFund');
                    $this->PledgeFund = new PledgeFund();
                    $shipping_info['PledgeFund']['shipping_address'] = $data['ProjectFund']['address'];
                    $shipping_info['PledgeFund']['zip_code'] = $data['ProjectFund']['zip_code'];
                    if (!$this->PledgeFund->validates()) {
                        if (!empty($this->PledgeFund->validationErrors['address'])) $validationErrors['address'] = $this->PledgeFund->validationErrors['address'];
                        if (!empty($this->PledgeFund->validationErrors['zip_code'])) $validationErrors['zip_code'] = $this->PledgeFund->validationErrors['zip_code'];
                    }
                }
            }
            $event->data['error'] = $validationErrors;
        }
    }
    public function onProjectFundAdd($event) 
    {
        $data = $event->data['data'];
        if (!empty($data['ProjectFund']['project_reward_id'])) {
            App::import('Model', 'Projects.ProjectReward');
            $this->ProjectReward = new ProjectReward();
            $project_id = $data['ProjectFund']['project_id'];
            App::import('Model', 'Pledge.Pledge');
            $this->Pledge = new Pledge();
            $project = $this->Pledge->Project->find('first', array(
                'conditions' => array(
                    'Project.id' => $project_id
                ) ,
                'recursive' => -1
            ));
            if ($project['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
                App::import('Model', 'Pledge.PledgeFund');
                $this->PledgeFund = new PledgeFund();
                $reward = $this->ProjectReward->find('first', array(
                    'conditions' => array(
                        'ProjectReward.id' => $data['ProjectFund']['project_reward_id']
                    )
                ));
                if (!empty($reward) && $reward['ProjectReward']['is_shipping']) {
                    $_data['PledgeFund']['project_fund_id'] = $data['ProjectFund']['id'];
                    $_data['PledgeFund']['project_reward_id'] = $data['ProjectFund']['project_reward_id'];
                    $_data['PledgeFund']['shipping_address'] = $data['ProjectFund']['address'];
                    $_data['PledgeFund']['zip_code'] = $data['ProjectFund']['zip_code'];
                    if (!empty($data['ProjectFund']['country_id'])) {
                        $_data['PledgeFund']['country_id'] = $this->PledgeFund->Country->findCountryId($data['ProjectFund']['country_id']);
                    }
                    $_data['State']['country_id'] = $_data['PledgeFund']['country_id'];
                    $_data['PledgeFund']['state_id'] = !empty($data['State']['id']) ? $data['State']['id'] : $this->Project->State->findOrSaveAndGetIdWithArray($data['State']['name'], $data['State']);
                    $_data['City']['state_id'] = $_data['PledgeFund']['state_id'];
                    $_data['City']['country_id'] = $data['ProjectFund']['country_id'];
                    $_data['PledgeFund']['city_id'] = !empty($data['City']['id']) ? $data['City']['id'] : $this->Project->City->findOrSaveAndGetIdWithArray($data['City']['name'], $data['City']);
                }
                if (!empty($reward) && $reward['ProjectReward']['is_having_additional_info']) {
                    $_data['PledgeFund']['additional_info'] = !empty($data['ProjectFund']['additional_info']) ? $data['ProjectFund']['additional_info'] : "";
                }
                if (!empty($_data)) {
                    $this->PledgeFund->save($_data);
                }
            }
        }
    }
    public function onOpenFunding($event) 
    {
        $controller = $event->subject();
        if (is_object($controller->Project)) {
            $obj = $controller->Project;
        } else {
            $obj = $controller;
        }
        $event_data = $event->data['data'];
        $type = $event->data['type'];
        $project = $obj->find('first', array(
            'conditions' => array(
                'Project.id' => $event_data['project_id']
            ) ,
            'contain' => array(
                'Pledge'
            ) ,
            'recursive' => 0
        ));
        if ($project['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            if (isPluginEnabled('Idea') && ($type == 'approve' || $type == 'vote')) {
				
                if ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::Pending) {
                    $obj->Pledge->updateStatus(ConstPledgeProjectStatus::OpenForIdea, $event_data['project_id']);
                    $event->data['message'] = Configure::read('project.alt_name_for_project_singular_caps').__l(' has been opened for voting');
                } else {
                    $event->data['error_message'] = Configure::read('project.alt_name_for_project_singular_caps').__l(' has been already opened for voting');
                }
            } else {
                if ($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::Pending || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForIdea) {
                    $obj->Pledge->updateStatus(ConstPledgeProjectStatus::OpenForFunding, $event_data['project_id']);
                    $event->data['message'] = sprintf(__l('%s has been opened for %s') , Configure::read('project.alt_name_for_project_singular_caps') , Configure::read('project.alt_name_for_pledge_present_continuous_small'));
                } else {
                    $event->data['error_message'] = sprintf(__l('%s has been already opened for %s') , Configure::read('project.alt_name_for_project_singular_caps') , Configure::read('project.alt_name_for_pledge_present_continuous_small'));
                }
            }
        }
    }
    public function onCategorydisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        $class = '';
		if(isset($event->data['class'])){
			$class = $event->data['class'];
		}
        $extra_arr = array();
        if (!empty($event->data['target'])) {
            $extra_arr['target'] = '_blank';
        }
        $return = '';
        if ($data['ProjectType']['id'] == ConstProjectTypes::Pledge) {
            App::import('Model', 'Pledge.Pledge');
            $Pledge = new Pledge;
            $pledge = $Pledge->find('first', array(
                'conditions' => array(
                    'Pledge.project_id' => $data['Project']['id']
                ) ,
                'contain' => array(
                    'PledgeProjectCategory'
                ) ,
                'recursive' => 0
            ));
            if (!empty($pledge['PledgeProjectCategory'])) {
                if ($class == 'categoryname') {
                    $return = $pledge['PledgeProjectCategory']['name'];
                } else {
                    if ($pledge['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForIdea) {
                        $return.= $obj->Html->link($pledge['PledgeProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $pledge['PledgeProjectCategory']['slug'],
                            'project_type' => 'pledge',
                            'idea' => 'idea'
                        ) , array_merge(array(
                            'title' => $pledge['PledgeProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    } else {
                        $return.= $obj->Html->link($pledge['PledgeProjectCategory']['name'], array(
                            'controller' => 'projects',
                            'action' => 'index',
                            'category' => $pledge['PledgeProjectCategory']['slug'],
                            'project_type' => 'pledge',
                        ) , array_merge(array(
                            'title' => $pledge['PledgeProjectCategory']['name'],
                            'class' => 'text-danger' .$class
                        ) , $extra_arr));
                    }
                }
            }
            $event->data['content'] = $return;
        }
    }
    public function onMessageDisplay($event) 
    {
        $obj = $event->subject();
        $data = $event->data['data'];
        App::import('Model', 'Pledge.Pledge');
        $Pledge = new Pledge;
        $projectIds = $Pledge->find('list', array(
            'conditions' => array(
                'Pledge.pledge_project_status_id' => array(
                    ConstPledgeProjectStatus::OpenForFunding,
                    ConstPledgeProjectStatus::GoalReached,
                ) ,
                'Pledge.user_id' => $obj->Auth->user('id') ,
            ) ,
            'fields' => array(
                'Pledge.project_id'
            )
        ));
        $projectIds = array_unique(array_merge($projectIds, $data));
        $event->data['ids'] = $projectIds;
        $event->data['projectStatus'] = $this->__getProjectStatus($projectIds);
    }
    public function __getProjectStatus($projectIds) 
    {
        App::import('Model', 'Pledge.Pledge');
        $Pledge = new Pledge;
        $pledges = $Pledge->find('all', array(
            'conditions' => array(
                'Pledge.project_id' => $projectIds,
            ) ,
            'contain' => array(
                'PledgeProjectStatus'
            ) ,
            'recursive' => 0
        ));
        $projectDetails = array();
        foreach($pledges as $key => $pledge) {
            $projectDetails[$pledge['Pledge']['project_id']] = $pledge['PledgeProjectStatus'];
        }
        return $projectDetails;
    }
    public function getProjectStatusValue($event) 
    {
        $projectStatusIds = $event->data['status_id'];
        $projectTypeId = $event->data['project_type_id'];
        if ($projectTypeId == ConstProjectTypes::Pledge) {
            $pledgeProjectStatus = array(
                ConstPledgeProjectStatus::Pending => __l('Pending') ,
                ConstPledgeProjectStatus::OpenForFunding => __l('Open for Funding') ,
                ConstPledgeProjectStatus::FundingClosed => __l('Funding Closed') ,
                ConstPledgeProjectStatus::FundingExpired => sprintf(__l('%s Expired') , Configure::read('project.alt_name_for_project_singular_caps')) ,
                ConstPledgeProjectStatus::ProjectCanceled => sprintf(__l('%s Canceled') , Configure::read('project.alt_name_for_project_singular_caps')) ,
                ConstPledgeProjectStatus::GoalReached => __l('Goal Reached') ,
                ConstPledgeProjectStatus::OpenForIdea => __l('Open for voting')
            );
            if (array_key_exists($projectStatusIds, $pledgeProjectStatus)) {
                $event->data['response'] = $pledgeProjectStatus[$projectStatusIds];
            } else {
                $event->data['response'] = 0;
            }
        }
    }
    public function onMessageInbox($event) 
    {
        $obj = $event->subject();
        $projectStatus = $event->data['projectStatus'];
        $project = $event->data['project'];
        if (!empty($project['Project']['project_type_id']) && $project['Project']['project_type_id'] == ConstProjectTypes::Pledge) {
            $projectStatusNew = $this->__getProjectStatus($project['Project']['id']);
            if (!empty($event->data['type']) && $event->data['type'] == 'status') {
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::FundingClosed
                ))) {
                    $event->data['is_allow_to_print_voucher'] = 1;
                    $event->data['is_allow_to_change_given'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForFunding
                ))) {
                    $event->data['is_allow_to_cancel_pledge'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_allow_to_vote'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                } elseif (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_move_for_voting'] = 1;
                    $event->data['is_allow_to_move_for_funding'] = 1;
                    if (isPluginEnabled('Idea')) {
                        $event->data['is_show_vote'] = 1;
                    }
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::FundingClosed,
                    ConstPledgeProjectStatus::GoalReached
                ))) {
                    $event->data['is_allow_to_change_status'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForFunding,
                    ConstPledgeProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_cancel_project'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::ProjectCanceled,
                    ConstPledgeProjectStatus::FundingExpired
                ))) {
                    $event->data['is_allow_to_follow'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::Pending,
                    ConstPledgeProjectStatus::ProjectCanceled
                ))) {
                    $event->data['is_affiliate_status_pending'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::FundingClosed
                ))) {
                    $event->data['is_not_show_you_here'] = 1;
                }
                if (!in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::Pending,
                    ConstPledgeProjectStatus::OpenForIdea
                ))) {
                    $event->data['is_show_project_funding_tab'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForFunding,
                    ConstPledgeProjectStatus::GoalReached
                ))) {
                    $event->data['is_allow_to_fund'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForIdea,
                    ConstPledgeProjectStatus::GoalReached,
                    ConstPledgeProjectStatus::OpenForFunding
                ))) {
                    $event->data['is_allow_to_share'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::FundingClosed
                ))) {
                    $event->data['is_allow_to_mange_reward'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::Pending
                ))) {
                    $event->data['is_allow_to_pay_listing_fee'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    0,
                    ConstPledgeProjectStatus::Pending,
                    ConstPledgeProjectStatus::OpenForIdea
                )) || (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::OpenForFunding
                )) && Configure::read('Project.is_allow_project_owner_to_edit_project_in_open_status'))) {
                    $event->data['is_allow_to_edit_fund'] = 1;
                }
                if (in_array($projectStatusNew[$project['Project']['id']]['id'], array(
                    ConstPledgeProjectStatus::Pending
                ))) {
                    $event->data['is_pending_status'] = 1;
                }
            }
            if (empty($projectStatus)) {
                $event->data['projectStatus'] = $projectStatusNew;
            } else {
                $event->data['projectStatus'] = $projectStatusNew+$projectStatus;
            }
        }
    }
    public function getClosedProjectIds($event) 
    {
        $obj = $event->subject();
        $project_ids = $event->data['project_ids'];
        $status_id = ConstPledgeProjectStatus::FundingClosed;
        $conditions = array();
        $conditions['Pledge.project_id'] = $project_ids;
        $conditions['Pledge.pledge_project_status_id'] = $status_id;
        $tmp_project_ids = $this->__getProjectIds($conditions);
        $conditions = array();
        $conditions['Pledge.user_id'] = $obj->Auth->user('id');
        $conditions['Pledge.pledge_project_status_id'] = $status_id;
        $tmp1_project_ids = $this->__getProjectIds($conditions);
        $event->data['project_ids'] = array_unique(array_merge($tmp_project_ids, $tmp1_project_ids));
    }
    public function __getProjectIds($conditions) 
    {
        App::import('Model', 'Pledge.Pledge');
        $pledge = new Pledge();
        $projectIds = $pledge->find('list', array(
            'conditions' => $conditions,
            'fields' => array(
                'Pledge.project_id'
            )
        ));
        return $projectIds;
    }
    public function getConditions($event) 
    {
        if (!empty($event->data['data'])) {
            $data = $event->data['data'];
        }
        if (!empty($event->data['type'])) {
            $type = $event->data['type'];
        }
        if (!empty($event->data['page'])) {
            $page = $event->data['page'];
        }
        if (!empty($data) && $data['ProjectType']['id'] == ConstProjectTypes::Pledge) {
            if ($type == 'idea') {
                $event->data['conditions'] = array(
                    'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::OpenForIdea
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][]['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForFunding;
                $event->data['conditions']['OR'][]['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::GoalReached;
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][]['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForIdea;
                $event->data['conditions']['OR'][]['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForFunding;
                $event->data['conditions']['OR'][]['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::GoalReached;
            } elseif ($type == 'closed') {
                $event->data['conditions'] = array(
                    'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::FundingClosed
                );
            } elseif ($type == 'notclosed') {
                $event->data['conditions'] = array(
                    'Pledge.pledge_project_status_id != ' => ConstPledgeProjectStatus::FundingClosed
                );
            }
        } elseif (!empty($page)) {
            if ($type == 'idea') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::OpenForIdea
                );
            } elseif ($type == 'myprojects') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id NOT' => array(
                        ConstPledgeProjectStatus::Pending,
                        ConstPledgeProjectStatus::OpenForIdea,
                        ConstPledgeProjectStatus::ProjectCanceled,
                        ConstPledgeProjectStatus::FundingExpired,
                    )
                );
            } elseif ($type == 'search') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id NOT' => array(
                        ConstPledgeProjectStatus::Pending,
                    )
                );
            } elseif ($type == 'open') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForFunding,
                        ConstPledgeProjectStatus::GoalReached,
                    )
                );
            } elseif ($type == 'project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForFunding,
                        ConstPledgeProjectStatus::FundingClosed,
                        ConstPledgeProjectStatus::GoalReached
                    )
                );
            } elseif ($type == 'all_project_count') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id NOT' => array(
                        ConstPledgeProjectStatus::OpenForIdea,
                    )
                );
            } elseif ($type == 'idea_count') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'count') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForFunding,
                        ConstPledgeProjectStatus::FundingClosed,
                        ConstPledgeProjectStatus::GoalReached,
                        ConstPledgeProjectStatus::OpenForIdea
                    )
                );
            } elseif ($type == 'city_count') {
                $event->data['conditions']['OR'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForFunding,
                        ConstPledgeProjectStatus::GoalReached
                    )
                );
            } elseif ($type == 'iphone') {
                $event->data['conditions']['AND'][] = array(
                    'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::OpenForFunding,
                        ConstPledgeProjectStatus::GoalReached
                    )
                );
            }
        }
    }
    public function getContain($event) 
    {
        $obj = $event->subject();
        switch ($event->data['type']) {
            case 1:
                $event->data['contain']['Pledge'] = array(
                    'PledgeProjectCategory',
                    'PledgeProjectStatus',
                );
                break;

            case 2:
                $event->data['contain']['Pledge'] = array(
                    'fields' => array(
                        'id'
                    )
                );
                break;
        }
    }
    public function getProjectTypeStatus($event) 
    {
        $obj = $event->subject();
        $project = $event->data['project'];
        if (!empty($project['Pledge'])) {
            $data = array();
            $data['Project_funding_text'] = __l('Funding amount');
            $data['Project_funded_text'] = Configure::read('project.alt_name_for_pledge_past_tense_small');
            $data['Project_fund_button_lable'] = Configure::read('project.alt_name_for_pledge_singular_caps');
            $data['Project_status_name'] = $project['Pledge']['PledgeProjectStatus']['name'];
            if (($project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding || $project['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::GoalReached)) {
                if (($obj->Auth->user('id') != $project['Project']['user_id']) || Configure::read('Project.is_allow_owner_fund_own_project')) {
                    $data['Project_fund_button_status'] = true;
                    $data['Project_fund_button_url'] = Router::url(array(
                        'controller' => 'project_funds',
                        'action' => 'add',
                        $project['Project']['id']
                    ) , true);
                } else {
                    $data['Project_fund_button_status'] = false;
                    $data['Project_fund_button_url'] = '';
                }
            } else {
                $data['Project_fund_button_status'] = false;
            }
            if ((strtotime($project['Project']['project_end_date']) < strtotime(date('Y-m-d'))) && ($project['Project']['needed_amount'] != $project['Project']['collected_amount'])) {
                $data['Project_status'] = -1;
            } else if ($project['Project']['needed_amount'] == $project['Project']['collected_amount']) {
                $data['Project_status'] = 1;
            } else {
                $data['Project_status'] = 0;
            }
            $data['Pledged'] = $project['Project']['collected_amount'];
            $data['Category_name'] = $project['PledgeProjectCategory']['name'];
            $event->data['data'] = $data;
        }
    }
    public function howitworks($event) 
    {
        $view = $event->subject();
        App::import('Model', 'PaymentGatewaySetting');
        $this->PaymentGatewaySetting = new PaymentGatewaySetting();
        $arrPledgeWallet = $this->PaymentGatewaySetting->find('first', array(
            'conditions' => array(
                'PaymentGatewaySetting.payment_gateway_id' => ConstPaymentGateways::Wallet,
                'PaymentGatewaySetting.name' => 'is_enable_for_pledge'
            ) ,
            'recursive' => 0
        ));
        if ($arrPledgeWallet['PaymentGateway']['is_test_mode']) {
            $data['is_pledge_wallet_enabled'] = $arrPledgeWallet['PaymentGatewaySetting']['test_mode_value'];
        } else {
            $data['is_pledge_wallet_enabled'] = $arrPledgeWallet['PaymentGatewaySetting']['live_mode_value'];
        }
        if (isPluginEnabled('Sudopay')) {
            App::import('Model', 'Sudopay.SudopayPaymentGateway');
            $this->SudopayPaymentGateway = new SudopayPaymentGateway();
            $supported_gateways = $this->SudopayPaymentGateway->find('list', array(
                'fields' => array(
                    'SudopayPaymentGateway.sudopay_gateway_name'
                ) ,
                'recursive' => -1,
            ));
            $data['supported_gateways'] = $supported_gateways;
        }
        echo $view->element('Pledge.how_it_works', $data);
    }
    public function onActionToBeTakenRender($event) 
    {
        $view = $event->subject();
        App::import('Model', 'User');
        $user = new User();
        App::import('Model', 'Pledge.Pledge');
        $pledge = new Pledge();
        $data['pledge_pending_for_approval_count'] = $pledge->Project->find('count', array(
            'conditions' => array(
                'Project.project_type_id' => ConstProjectTypes::Pledge,
                'Project.is_pending_action_to_admin = ' => 1
            ) ,
            'recursive' => -1
        ));
        $data['pledge_user_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_user_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Pledge
            ) ,
            'recursive' => -1
        ));
        $data['pledge_system_flagged_count'] = $user->Project->find('count', array(
            'conditions' => array(
                'Project.is_system_flagged' => 1,
                'Project.project_type_id' => ConstProjectTypes::Pledge
            ) ,
            'recursive' => -1
        ));
        $event->data['content']['PendingProject'].= $view->element('Pledge.admin_action_taken_pending', $data);
        $event->data['content']['FlaggedProjects'].= $view->element('Pledge.admin_action_taken', $data);
    }
    public function getFeatureProjectList($event) 
    {
        $controller = $event->subject();
		$conditions = array();
		$conditions['Project.is_active'] = 1;
		$conditions['Project.is_draft'] = 0;
		$conditions['Project.is_admin_suspended'] = '0';
		$conditions['Project.project_end_date >= '] = date('Y-m-d');
		$conditions['Project.project_type_id'] = ConstProjectTypes::Pledge;		
		
		$conditions['NOT'] = array( 'Pledge.pledge_project_status_id' => array(
                        ConstPledgeProjectStatus::Pending,
						ConstPledgeProjectStatus::FundingExpired,
						ConstPledgeProjectStatus::ProjectCanceled
                    ));
		
		$contain = array(
			'Attachment',
			'Pledge'
		);
		$order = array(
			'Project.is_featured' => 'desc',
			'Project.id' => 'desc'
		);            
		$pledge = $controller->Project->find('all', array(
			'conditions' => $conditions,
			'contain' => $contain,
			'recursive' => 3,
			'order' => $order,
			'limit' => 4
		));
		$event->data['content']['Pledge'] = $pledge;
    }
}
?>