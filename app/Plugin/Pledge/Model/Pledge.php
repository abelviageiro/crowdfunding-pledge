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
class Pledge extends AppModel
{
    public $name = 'Pledge';
    var $useTable = 'project_pledge_fields';
    public $displayField = 'id';
    public $actsAs = array(
        'Sluggable' => array(
            'label' => array(
                'name'
            )
        ) ,
    );
    //$validate set in __construct for multi-language support
    //The Associations below have been created with all possible keys, those that are not needed can be removed
    public $belongsTo = array(
        'PledgeProjectCategory' => array(
            'className' => 'Pledge.PledgeProjectCategory',
            'foreignKey' => 'pledge_project_category_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'PledgeProjectStatus' => array(
            'className' => 'Pledge.PledgeProjectStatus',
            'foreignKey' => 'pledge_project_status_id',
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'counterCache' => true,
            'counterScope' => '',
        ) ,
        'Project' => array(
            'className' => 'Projects.Project',
            'foreignKey' => 'project_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ) ,
    );
    function __construct($id = false, $table = null, $ds = null) 
    {
        parent::__construct($id, $table, $ds);
        $this->_permanentCacheAssociations = array(
            'Project'
        );
        $this->validate = array(
            'project_funding_end_date' => array(
                'rule2' => array(
                    'rule' => array(
                        'comparison',
                        '>=',
                        date('Y-m-d') ,
                    ) ,
                    'message' => sprintf(__l('%s funding end date should be greater than to today') , Configure::read('project.alt_name_for_project_singular_caps'))
                ) ,
                'rule1' => array(
                    'rule' => 'date',
                    'message' => __l('Enter valid date')
                )
            ) ,
            'pledge_project_category_id' => array(
                'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required')
                )
            ),
			'min_amount_to_fund' => array(
                'rule3' => array(
                    'rule' => '_checkRewardAmount',
                    'allowEmpty' => false,
                    'message' => __l('Required') ,
                ), 
				'rule2' => array(
                    'rule' => array(
                        'comparison',
                        '>=',
                        1
                    ) ,
                    'allowEmpty' => false,
                    'message' => __l('Must be greater than zero')
                ) ,	
				'rule1' => array(
                    'rule' => 'notempty',
                    'allowEmpty' => false,
                    'message' => __l('Required') ,
                ) 
            ) 
        );
    }
	
	function _checkRewardAmount()
    {
        if ($this->data['Pledge']['pledge_type_id'] != ConstPledgeTypes::Any) {
			if($this->data['Pledge']['min_amount_to_fund'] < 1){
				return __l('Must be greater than zero');
			} else if ($this->data['Pledge']['min_amount_to_fund'] < 1 && ($this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Minimum || $this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Fixed || $this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Multiple)) {
                return __l('Must be greater than zero');
            } else if ($this->data['Pledge']['min_amount_to_fund'] > $this->data['Project']['needed_amount'] && ($this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Minimum || $this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Fixed || $this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Multiple)) {
                return __l('The amount should be less than needed amount');
            } else if ((($this->data['Project']['needed_amount'] % $this->data['Pledge']['min_amount_to_fund']) != 0) && ($this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Multiple || $this->data['Pledge']['pledge_type_id'] == ConstPledgeTypes::Fixed) && !$this->data['Pledge']['is_allow_over_funding']) {
                return __l('Amount cannot be equally shared or else you should allow over funding.');
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    function minMaxAmount($field1, $field = null) 
    {
        return ($this->data[$this->name][$field] >= Configure::read('Project.minimum_amount') && $this->data[$this->name][$field] <= Configure::read('Project.maximum_amount'));
    }
    function updateProjectStatus($project_fund_id) 
    {
        $projectFund = $this->Project->ProjectFund->find('first', array(
            'conditions' => array(
                'ProjectFund.id' => $project_fund_id
            ) ,
            'contain' => array(
                'Project' => array(
                    'Pledge',
                ) ,
            ) ,
            'recursive' => 2
        ));
        if ($projectFund['Project']['collected_amount'] == $projectFund['Project']['needed_amount'] && empty($projectFund['Project']['Pledge']['is_allow_over_funding'])) {
            $this->updateStatus(ConstPledgeProjectStatus::FundingClosed, $projectFund['Project']['id']);
        } elseif ($projectFund['Project']['collected_amount'] >= $projectFund['Project']['needed_amount']) {
            if ($projectFund['Project']['Pledge']['pledge_project_status_id'] == ConstPledgeProjectStatus::OpenForFunding && $projectFund['Project']['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::GoalReached) {
                $this->updateStatus(ConstPledgeProjectStatus::GoalReached, $projectFund['Project']['id']);
            }
        }
    }
    function updateStatus($to_project_status_id, $project_id) 
    {
        $project = $this->Project->find('first', array(
            'conditions' => array(
                'Project.id' => $project_id,
            ) ,
            'contain' => array(
                'Pledge',
                'User',
                'ProjectType',
                'Attachment',
            ) ,
            'recursive' => 0,
        ));
        $_data = array();
        $_data['Pledge']['pledge_project_status_id'] = $to_project_status_id;
		
        if ($to_project_status_id == ConstPledgeProjectStatus::GoalReached || ($to_project_status_id == ConstPledgeProjectStatus::FundingClosed && $project['Pledge']['pledge_project_status_id'] != ConstPledgeProjectStatus::GoalReached)) {
            $_data['Pledge']['project_fund_goal_reached_date'] = date('Y-m-d H:i:s');
        }
        if ($to_project_status_id == ConstPledgeProjectStatus::ProjectCanceled) {
            $_data['Project']['project_cancelled_date'] = date('Y-m-d H:i:s');
        }
        $_data['Pledge']['id'] = $project['Pledge']['id'];
        $this->save($_data);
        $tmp_project = $this->{'processStatus' . $to_project_status_id}($project);
            $_data = array();
            $_data['from_project_status_id'] = $project['Pledge']['pledge_project_status_id'];
            $_data['to_project_status_id'] = $to_project_status_id;
            $this->postActivity($project, ConstProjectActivities::StatusChange, $_data);
            //Expired or Canceled only hide in activities
            if ($to_project_status_id == 4 || $to_project_status_id == 5) {
                // update activities record hide from public
                $this->Project->Message->updateActivitiesHideFromPublic($project_id);
            }
        }
        function processStatus2($project) 
        {
            // Open For Funding //
            if (isPluginEnabled('SocialMarketing')) {
                App::import('Model', 'SocialMarketing.UserFollower');
                $this->UserFollower = new UserFollower();
                $this->UserFollower->send_follow_mail($_SESSION['Auth']['User']['id'], 'added', $project);
            }
            $data['Project']['project_start_date'] = date('Y-m-d');
            $data['Project']['id'] = $project['Project']['id'];
            $this->Project->save($data);
            $total_needed_amount = $project['User']['total_needed_amount']+$project['Project']['needed_amount'];
            $this->Project->updateAll(array(
                'User.total_needed_amount' => $total_needed_amount
            ) , array(
                'User.id' => $project['User']['id']
            ));
            $this->Project->postOnSocialNetwork($project);
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
        function processStatus3($project) 
        {
            // Funding Closed //
            // capture backed amount
            $this->Project->_executepay($project['Project']['id']);
        }
        function processStatus4($project) 
        {
            // Project Expired //
            // refund backed amount to backer
            $this->Project->_refund($project['Project']['id']);
        }
        function processStatus5($project) 
        {
            // Project Canceled //
            // refund backed amount to backer
            $data['Project']['project_cancelled_date'] = date('Y-m-d H:i:s');
            $data['Project']['id'] = $project['Project']['id'];
            $this->Project->save($data);
            $this->Project->_refund($project['Project']['id'], 1);
        }
        function processStatus6() 
        {
            // Goal Reached //
            
        }
        function processStatus8($project) 
        {
            // Open For Idea //
            $data = array();
            $data['User']['id'] = $project['Project']['user_id'];
            $data['User']['is_idle'] = 0;
            $data['User']['is_project_posted'] = 1;
            $this->Project->User->save($data);
        }
		public function deductFromCollectedAmount($project) 
		{
			$projectTypeName = ucwords($project['ProjectType']['name']);
			$pledges = $this->find('all', array(
				'conditions' => array(
					'Pledge.project_id' => $project['Project']['id'],
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
			if (in_array($projectDetails[$project['Project']['id']]['id'], array(
							ConstPledgeProjectStatus::ProjectCanceled,
							ConstPledgeProjectStatus::FundingExpired
					))) {
				return false;
			} else {
				return true;
			}
		}
		public function getCategoryConditions($category = null, $is_slug = true) 
		{
			if(!empty($is_slug)) {
				App::import('Model', 'Pledge.PledgeProjectCategory');
				$this->PledgeProjectCategory = new PledgeProjectCategory();
				$category = $this->PledgeProjectCategory->find('first', array(
					'conditions' => array(
						'PledgeProjectCategory.slug' => $category
					) ,
					'recursive' => -1
				));
				$response['category_details'] = $category['PledgeProjectCategory'];
				$response['conditions'] = array(
					'Pledge.pledge_project_category_id' => $category['PledgeProjectCategory']['id']
				);
			} else {
				$response['conditions'] = array(
					'Pledge.pledge_project_category_id' => $category
				);
			}
			return $response;
		}
		public function onProjectCategories($is_slug = false) 
		{
			$fields = array(
				'PledgeProjectCategory.slug',
				'PledgeProjectCategory.name'
			);
			if(!$is_slug) {
				$fields = array(
					'PledgeProjectCategory.id',
					'PledgeProjectCategory.name'
				);
			}		
			$pledgeProjectCategory = $this->PledgeProjectCategory->find('list', array(
				'conditions' => array(
					'PledgeProjectCategory.is_approved' => 1
				) ,
				'fields' => $fields,
				'order' => array(
					'PledgeProjectCategory.name' => 'ASC'
				) ,
			));
			$response['pledgeCategories'] = $pledgeProjectCategory;
			return $response;
		}
		public function isAllowToPublish($project_id) 
		{
			$project = $this->find('count', array(
				'conditions' => array(
					'Pledge.project_id' => $project_id,
					'Pledge.pledge_project_status_id' => array(
						ConstPledgeProjectStatus::OpenForIdea,
						ConstPledgeProjectStatus::GoalReached,
						ConstPledgeProjectStatus::OpenForFunding
					)
				)
			));
			$response['is_allow_to_publish'] = 1;
			return $response;
		}
		public function isAllowToProcessPayment($project_id) 
		{
				$project = $this->find('count', array(
					'conditions' => array(
						'Pledge.project_id' => $project_id,
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::Pending,
						'Project.is_paid' => 0,
					) ,
					'recursive' => 0
				));
				if (!empty($project)) {
					$response['is_allow_process_payment'] = 1;
					return $response;
				}
		}
		public function isAllowToViewProject($project, $funded_users, $followed_user) 
		{
			$response['is_allow_to_view_project'] = 1;
			if ((in_array($project['Pledge']['pledge_project_status_id'], array(
				ConstPledgeProjectStatus::Pending,
				ConstPledgeProjectStatus::FundingExpired,
				ConstPledgeProjectStatus::ProjectCanceled
			))) && (!$funded_users) && (!$followed_user) && (!$_SESSION['Auth']['User']['id'] || ($_SESSION['Auth']['User']['id'] && $_SESSION['Auth']['User']['id'] != $project['Project']['user_id'] && (!$funded_users) && $_SESSION['Auth']['User']['role_id'] != ConstUserTypes::Admin))) {
				$response['is_allow_to_view_project'] = 0;
			}
			return $response;
		}
		public function onProjectViewMessageDisplay($project) 
		{
			$pledge = $this->find('first', array(
				'conditions' => array(
					'Pledge.pledge_project_status_id' => array(
						ConstPledgeProjectStatus::OpenForIdea,
						ConstPledgeProjectStatus::OpenForFunding,
						ConstPledgeProjectStatus::GoalReached,
						ConstPledgeProjectStatus::FundingClosed,
					) ,
					'Pledge.project_id' => $project['Project']['id']
				) ,
				'fields' => array(
					'Pledge.project_id'
				)
			));
			$response['is_comment_allow'] = 0;
			if (!empty($pledge)) {
				$response['is_comment_allow'] = 1;
			}
			return $response;
		}
		public function getUserOpenProjectCount($user_id){
			$pledge_count = $this->Project->find('count',array(
    			'conditions' => array(
    					'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::OpenForFunding,
    					'Project.user_id' => $user_id,
    			) ,
    			'contain' => array(
                    'Pledge'
                ) ,
                'recursive' => 0
    	));
			return $pledge_count;
		}    
}
?>