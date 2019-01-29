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
class PledgesController extends AppController
{
	public $name = 'Pledges';
	public function beforeFilter()
	{
		$this->Security->disabledFields = array(
				'Project.id',
		);
		parent::beforeFilter();
	}
	public function overview()
	{
		$user_id = $this->Auth->user('id');
		if (!empty($user_id)) {
			$periods = array(
					'day' => array(
							'display' => __l('Today') ,
							'conditions' => array(
									'Project.created =' => date('Y-m-d', strtotime('now')) ,
							)
					) ,
					'week' => array(
							'display' => __l('This Week') ,
							'conditions' => array(
									'Project.created =' => date('Y-m-d', strtotime('now -7 days')) ,
							)
					) ,
					'month' => array(
							'display' => __l('This Month') ,
							'conditions' => array(
									'Project.created =' => date('Y-m-d', strtotime('now -30 days')) ,
							)
					) ,
					'total' => array(
							'display' => __l('Total') ,
							'conditions' => array()
					)
			);
			$models[] = array(
					'Transaction' => array(
							'display' => __l('Cleared') ,
							'projectconditions' => array(
									'Project.user_id' => $user_id,
									'Pledge.pledge_project_status_id' => array(
											ConstPledgeProjectStatus::FundingClosed,
											ConstPledgeProjectStatus::GoalReached,
									)
							) ,
							'alias' => 'Cleared',
							'type' => 'cInt',
							'isSub' => 'Project',
							'class' => 'highlight-cleared'
					)
			);
			$models[] = array(
					'Transaction' => array(
							'display' => __l('Pipeline') ,
							'projectconditions' => array(
									'Project.user_id' => $user_id,
									'Pledge.pledge_project_status_id' => array(
											ConstPledgeProjectStatus::Pending,
											ConstPledgeProjectStatus::OpenForFunding,
											ConstPledgeProjectStatus::OpenForIdea,
									)
							) ,
							'alias' => 'Pipeline',
							'type' => 'cInt',
							'isSub' => 'Projects',
							'class' => 'highlight-pipeline'
					)
			);
			$models[] = array(
					'Transaction' => array(
							'display' => __l('Lost') ,
							'projectconditions' => array(
									'Project.user_id' => $user_id,
									'Pledge.pledge_project_status_id' => array(
											ConstPledgeProjectStatus::FundingExpired,
											ConstPledgeProjectStatus::ProjectCanceled
									)
							) ,
							'alias' => 'Lost',
							'type' => 'cInt',
							'isSub' => 'PropertyUsers',
							'class' => 'highlight-lost'
					)
			);
			foreach($models as $unique_model) {
				foreach($unique_model as $model => $fields) {
					foreach($periods as $key => $period) {
						if ($fields['alias'] == 'Cleared') {
							$period['conditions'] = array_merge($period['conditions'], array(
									'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
							));
						} elseif ($fields['alias'] == 'Pipeline') {
							$period['conditions'] = array_merge($period['conditions'], array(
									'Transaction.transaction_type_id' => ConstTransactionTypes::ProjectBacked
							));
						} elseif ($fields['alias'] == 'PipelineReverse') {
							$period['conditions'] = array_merge($period['conditions'], array(
									'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
							));
						} elseif ($fields['alias'] == 'Lost') {
							$period['conditions'] = array_merge($period['conditions'], array(
									'Transaction.transaction_type_id' => ConstTransactionTypes::Refunded
							));
						}
						$conditions = $period['conditions'];
						if (!empty($fields['conditions'])) {
							$conditions = array_merge($periods[$key]['conditions'], $fields['conditions']);
						}
						$projectConditions = array(
								'Project.user_id' => $this->Auth->user('id')
						);
						if (!empty($fields['projectconditions'])) {
							$projectConditions = $fields['projectconditions'];
						}
						$project_list = $this->Pledge->Project->find('list', array(
								'conditions' => $projectConditions,
								'fields' => array(
										'Project.id',
								) ,
								'recursive' => 1
						));
						$conditions['ProjectFund.project_id'] = $project_list;
						$conditions['Transaction.class'] = 'ProjectFund';
						$aliasName = !empty($fields['alias']) ? $fields['alias'] : $model;
						$result = $this->Pledge->Project->Transaction->find('first', array(
								'fields' => array(
										'SUM(Transaction.amount) as amount',
								) ,
								'conditions' => $conditions,
								'recursive' => 1
						));
						$this->set($aliasName . $key, $result[0]['amount']);
					}
				}
			}
		}
		$this->set(compact('periods', 'models'));
	}
	public function myprojects()
	{
		$conditions['Project.project_type_id'] = ConstProjectTypes::Pledge;
		$conditions['Project.user_id'] = $this->Auth->user('id');
		$order = array(
				'Project.project_end_date' => 'asc'
		);
		if (!$this->Auth->user('id')) {
	            if ($this->RequestHandler->prefers('json')){
			$this->set('iphone_response', array("message" =>__l('Invalid request') , "error" => 1));
		    }else{
			throw new NotFoundException(__l('Invalid request'));
		    }
		}
		if (!empty($this->request->params['named']['status'])) {
			if ($this->request->params['named']['status'] == 'pending') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::Pending;
			} elseif ($this->request->params['named']['status'] == 'idea') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForIdea;
			} elseif ($this->request->params['named']['status'] == 'cancelled') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::ProjectCanceled;
				unset($conditions['Project.project_end_date >= ']);
			} elseif ($this->request->params['named']['status'] == 'expired') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::FundingExpired;
				unset($conditions['Project.project_end_date >= ']);
			} elseif ($this->request->params['named']['status'] == 'closed') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::FundingClosed;
			} elseif ($this->request->params['named']['status'] == 'goal') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::GoalReached;
			} elseif ($this->request->params['named']['status'] == 'draft') {
				$conditions['Project.is_draft'] = 1;
			} elseif ($this->request->params['named']['status'] == 'open_for_funding') {
				$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForFunding;
			} elseif ($this->request->params['named']['status'] == 'flexible') {
				$conditions['Project.payment_method_id'] = ConstPaymentMethod::KiA;
			} elseif ($this->request->params['named']['status'] == 'fixed') {
				$conditions['Project.payment_method_id'] = ConstPaymentMethod::AoN;
			}
		}
		//Todo: Need to change for default status 
		/*else {
			$conditions['Pledge.pledge_project_status_id'] = ConstPledgeProjectStatus::OpenForFunding;
		}*/
		$heading = sprintf(__l('My %s') , Configure::read('project.alt_name_for_project_plural_caps'));
		$contain = array(
				'Project' => array(
						'ProjectType',
						'User' => array(
								'UserAvatar'
						) ,
						'Message' => array(
								'conditions' => array(
										'Message.is_activity' => 0,
										'Message.is_sender' => 0
								) ,
						) ,
						'Attachment',
						'Transaction',
				) ,
				'PledgeProjectStatus',
		);
		if (isPluginEnabled('ProjectRewards')) {
			$contain['Project']['ProjectReward'] = array();
		}
		if (isPluginEnabled('Idea')) {
			$contain['Project']['ProjectRating'] = array(
					'conditions' => array(
							'ProjectRating.user_id' => $this->Auth->user('id') ,
					)
			);
		}
		if (!isPluginEnabled('Idea')) {
			$conditions['Pledge.pledge_project_status_id !='] = ConstPledgeProjectStatus::OpenForIdea;
		}
		$this->paginate = array(
				'conditions' => $conditions,
				'contain' => $contain,
				'order' => $order,
				'recursive' => 3,
				'limit' => 20,
		);
		$projects = $this->paginate();
		$this->set('projects', $projects);
		
		if ($this->RequestHandler->prefers('json') && !empty($this->request->query['key'])) {
			$event_data['contain'] = $contain;
			$event_data['conditions'] = $conditions;
			$event_data['order'] = $order;
			$event_data['limit'] = 20;
			$event_data['model'] = "Pledge";
			$event_data = Cms::dispatchEvent('Controller.Pledge.myprojects', $this, array(
			    'data' => $event_data
			));
		}
	
		$pledgeStatuses = $this->Pledge->PledgeProjectStatus->find('list', array(
				'recursive' => -1
		));
		$projectStatuses = array();
		foreach($pledgeStatuses as $key => $status) {
			$status_condition = array(
					'Pledge.pledge_project_status_id ' => $key,
					'Project.user_id' => $this->Auth->user('id')
			);
			if ($key != ConstPledgeProjectStatus::ProjectCanceled) {
				$status_condition['Project.is_active'] = 1;
			}
			$project_status = $this->Pledge->Project->find('count', array(
					'conditions' => $status_condition,
					'contain' => array(
							'Pledge',
					) ,
					'recursive' => 0
			));
			$projectStatuses[$key] = $project_status;
		}
		$this->set('system_drafted', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_draft = ' => 1,
						'Project.user_id' => $this->Auth->user('id') ,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('projectStatuses', $projectStatuses);
		$count = $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_active' => 1,
						'Project.user_id' => $this->Auth->user('id') ,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		));
		$this->set('total_flexible_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.payment_method_id' => ConstPaymentMethod::KiA,
						'Project.project_type_id' => ConstProjectTypes::Pledge,
						'Project.user_id' => $this->Auth->user('id')
				) ,
				'recursive' => -1
		)));
		$this->set('total_fixed_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.payment_method_id' => ConstPaymentMethod::AoN,
						'Project.project_type_id' => ConstProjectTypes::Pledge,
						'Project.user_id' => $this->Auth->user('id')
				) ,
				'recursive' => -1
		)));
		$this->set('count', $count);
		if (!empty($this->request->params['named']['from'])) {
			$this->render('project_filter');
		}
		$countDetail = $this->Pledge->Project->getAdminRejectApproveCount(ConstProjectTypes::Pledge, ConstPledgeProjectStatus::Pending, 'Pledge', 'Pledge.pledge_project_status_id');
		$this->set('formFieldSteps', $countDetail['formFieldSteps']);
		$this->set('rejectedCount', $countDetail['rejectedCount']);
		$this->set('approvedCount', $countDetail['approvedCount']);
		$this->set('rejectedProjectIds', $countDetail['rejectedProjectIds']);
		$this->set('approvedProjectIds', $countDetail['approvedProjectIds']);
	}
	public function myfunds()
	{
		$conditions = array();
		$this->loadModel("Projects.ProjectFund");
		$conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Pledge;
		$conditions['ProjectFund.user_id'] = $this->Auth->user('id');
		if (empty($this->request->params['named']['status'])) {
			$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
		} else if ($this->request->params['named']['status'] == 'all') {
			$conditions['ProjectFund.project_fund_status_id <>'] = ConstProjectFundStatus::PendingToPay;
		} else if ($this->request->params['named']['status'] == 'backed') {
			$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
		} else if ($this->request->params['named']['status'] == 'refunded') {
			$conditions['ProjectFund.project_fund_status_id'] = array(
					ConstProjectFundStatus::Expired,
					ConstProjectFundStatus::Canceled,
			);
		} else if ($this->request->params['named']['status'] == 'paid') {
			$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
		}
		$this->set('fund_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.user_id' => $this->Auth->user('id') ,
						'ProjectFund.project_fund_status_id' => array(
								ConstProjectFundStatus::Authorized,
								ConstProjectFundStatus::PaidToOwner
						) ,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		/// Status Based on Count
		$this->set('backed_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.user_id' => $this->Auth->user('id') ,
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Authorized,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('refunded_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.user_id = ' => $this->Auth->user('id') ,
						'ProjectFund.project_fund_status_id' => array(
								ConstProjectFundStatus::Expired,
								ConstProjectFundStatus::Canceled,
						) ,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('paid_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.user_id = ' => $this->Auth->user('id') ,
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$contain = array(
				'User' => array(
						'UserAvatar'
				) ,
				'Project' => array(
						'User' => array(
								'fields' => array(
										'User.username',
										'User.id'
								)
						) ,
						'Pledge' => array(
								'PledgeProjectStatus'
						) ,
						'Attachment',
				)
		);
		if (isPluginEnabled('ProjectRewards')) {
			$contain['ProjectReward'] = array();
		}
		$paging_array = array(
				'conditions' => $conditions,
				'contain' => $contain,
				'recursive' => 3,
				'order' => array(
						'ProjectFund.id' => 'desc'
				)
		);
		$limit = 20;
		if (!empty($limit)) {
			$paging_array['limit'] = $limit;
		}
		$this->paginate = $paging_array;
		$this->set('projectFunds', $this->paginate('ProjectFund'));
		$this->set('all_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.user_id' => $this->Auth->user('id') ,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$conditions['ProjectFund.is_given'] = 1;
		$conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Pledge;
		$this->set('given_count', $this->ProjectFund->find('count', array(
				'conditions' => $conditions
		)));
		if (!empty($this->request->params['named']['from'])) {
			$this->render('pledge_filter');
		}
	}
	function admin_index()
	{
		$this->_redirectGET2Named(array(
				'filter_id',
				'project_category_id',
				'q'
		));
		if (!empty($this->request->data['Project']['q'])) {
			$this->request->params['named']['q'] = $this->request->data['Project']['q'];
		}
		App::import('Model', 'Projects.FormFieldStep');
		$FormFieldStep = new FormFieldStep();
		$formFieldSteps = $FormFieldStep->find('list', array(
				'conditions' => array(
						'FormFieldStep.project_type_id' => ConstProjectTypes::Pledge,
						'FormFieldStep.is_splash' => 1
				) ,
				'fields' => array(
						'FormFieldStep.order',
						'FormFieldStep.name'
				) ,
				'recursive' => -1
		));
		$this->set('formFieldSteps', $formFieldSteps);
		$this->pageTitle = Configure::read('project.alt_name_for_pledge_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps');
		$conditions = array();
		$conditions['Project.project_type_id'] = ConstProjectTypes::Pledge;
		// check the filer passed through named parameter
		if (isset($this->request->params['named']['filter_id'])) {
			$this->request->data['Project']['filter_id'] = $this->request->params['named']['filter_id'];
		}
		if (!empty($this->request->data['Project']['filter_id'])) {
			if ($this->request->data['Project']['filter_id'] == ConstMoreAction::Suspend) {
				$conditions['Project.is_admin_suspended'] = 1;
				$this->pageTitle.= ' - ' . __l('Suspended');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Active) {
				$conditions['Project.is_active'] = 1;
				$this->pageTitle.= ' - ' . __l('Active');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Inactive) {
				$conditions['Project.is_active'] = 0;
				$this->pageTitle.= ' - ' . __l('Inactive');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Featured) {
				$conditions['Project.is_featured'] = 1;
				$this->pageTitle.= ' - ' . __l('Featured');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Flagged) {
				$conditions['Project.is_system_flagged'] = 1;
				$this->pageTitle.= ' - ' . __l('System Flagged');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::UserFlagged) {
				$conditions['Project.is_user_flagged'] = 1;
				$this->pageTitle.= ' - ' . __l('User Flagged');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Drafted) {
				$conditions['Project.is_draft'] = 1;
				$this->pageTitle.= ' - ' . __l('Drafted');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Flexible) {
				$conditions['Project.payment_method_id'] = ConstPaymentMethod::KiA;
				$this->pageTitle.= ' - ' . __l('Flexible');
			} elseif ($this->request->data['Project']['filter_id'] == ConstMoreAction::Fixed) {
				$conditions['Project.payment_method_id'] = ConstPaymentMethod::AoN;
				$this->pageTitle.= ' - ' . __l('Fixed');
			}
			$this->request->params['named']['filter_id'] = $this->request->data['Project']['filter_id'];
		}
		if (!empty($this->request->data['Project']['project_status_id'])) {
			$this->request->params['named']['project_status_id'] = $this->request->data['Project']['project_status_id'];
			$conditions['Pledge.pledge_project_status_id'] = $this->request->data['Project']['project_status_id'];
		} elseif (!empty($this->request->params['named']['project_status_id'])) {
			$this->request->data['Project']['project_status_id'] = $this->request->params['named']['project_status_id'];
			$conditions['Pledge.pledge_project_status_id'] = $this->request->data['Project']['project_status_id'];
		} elseif (!empty($this->request->params['named']['is_allow_over_funding'])) {
			$this->request->data['Pledge']['is_allow_over_funding'] = $this->request->params['named']['is_allow_over_funding'];
			$conditions['Project.is_allow_over_funding'] = $this->request->data['Pledge']['is_allow_over_funding'];
		} elseif (!empty($this->request->params['named']['transaction_type_id']) && $this->request->params['named']['transaction_type_id'] == ConstTransactionTypes::ListingFee) {
			$this->pageTitle.= ' - ' . __l('Listing Fee Paid');
			$this->request->data['Project']['transaction_type_id'] = $this->request->params['named']['transaction_type_id'];
			$foreigns = $this->Pledge->Project->Transaction->find('list', array(
					'conditions' => array(
							'Transaction.class' => 'Project',
							'Transaction.transaction_type_id' => ConstTransactionTypes::ListingFee,
							'Project.project_type_id' => ConstProjectTypes::Pledge
					) ,
					'fields' => array(
							'Transaction.foreign_id'
					) ,
					'recursive' => 0
			));
			$conditions['Project.id'] = $foreigns;
		}
		if (!empty($this->request->data['Project']['project_status_id']) or !empty($this->request->data['Project']['project_status_id'])) {
			switch ($conditions['Pledge.pledge_project_status_id']) {
				case ConstPledgeProjectStatus::Pending:
					$this->pageTitle.= ' - ' . __l('Pending');
					break;

				case ConstPledgeProjectStatus::OpenForFunding:
					$this->pageTitle.= ' - ' . __l('Open for Funding');
					break;

				case ConstPledgeProjectStatus::OpenForIdea:
					$this->pageTitle.= ' - ' . __l('Open for Voting');
					break;

				case ConstPledgeProjectStatus::FundingClosed:
					$this->pageTitle.= ' - ' . __l('Funding Closed');
					break;

				case ConstPledgeProjectStatus::FundingExpired:
					$this->pageTitle.= ' - ' . __l('Funding Expired');
					break;

				case ConstPledgeProjectStatus::ProjectCanceled:
					$this->pageTitle.= ' - ' . __l('Canceled');
					break;

				case ConstPledgeProjectStatus::GoalReached:
					$this->pageTitle.= ' - ' . __l('Goal Reached');
					break;

				case ConstPledgeProjectStatus::PendingAction:
					$this->pageTitle.= ' - ' . __l('Pending Action to Admin');
					break;

				default:
					break;
			}
		}
		if (isset($this->request->params['named']['q'])) {
			$conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
			$this->request->data['Project']['q'] = $this->request->params['named']['q'];
		}
		if (!empty($this->request->params['named']['type']) && $this->request->params['named']['type'] == 'listing_fee') {
			$conditions['Project.fee_amount !='] = '0.00';
		}
		if (!empty($this->request->params['named']['project_flag_category_id'])) {
			$project_flag = $this->Pledge->Project->ProjectFlag->find('list', array(
					'conditions' => array(
							'ProjectFlag.project_flag_category_id' => $this->request->params['named']['project_flag_category_id'],
							'Project.project_type_id' => ConstProjectTypes::Pledge
					) ,
					'fields' => array(
							'ProjectFlag.id',
							'ProjectFlag.project_id'
					) ,
					'recursive' => 0
			));
			$conditions['Project.id'] = $project_flag;
		}
		if (!empty($this->request->params['named']['project_category_id'])) {
			$conditions['Pledge.pledge_project_category_id'] = $this->request->params['named']['project_category_id'];
			$pledgeProjectCategory = $this->Pledge->PledgeProjectCategory->find('first', array(
					'conditions' => array(
							'PledgeProjectCategory.id' => $this->request->params['named']['project_category_id']
					) ,
					'fields' => array(
							'PledgeProjectCategory.id',
							'PledgeProjectCategory.name'
					) ,
					'recursive' => -1
			));
			if (empty($pledgeProjectCategory)) {
				throw new NotFoundException(__l('Invalid request'));
			}
			$this->pageTitle.= ' - ' . $pledgeProjectCategory['PledgeProjectCategory']['name'];
		} elseif (!empty($this->request->params['named']['user_id'])) {
			$user = $this->{$this->modelClass}->User->find('first', array(
					'conditions' => array(
							'User.id' => $this->request->params['named']['user_id']
					) ,
					'fields' => array(
							'User.id',
							'User.username'
					) ,
					'recursive' => -1
			));
			if (empty($user)) {
				throw new NotFoundException(__l('Invalid request'));
			}
			$conditions['Project.user_id'] = $this->request->params['named']['user_id'];
			$this->pageTitle.= ' - ' . $user['User']['username'];
		}
		$contain = array(
				'User',
				'ProjectType',
				'Attachment',
				'Pledge' => array(
						'PledgeProjectStatus',
						'PledgeProjectCategory'
				) ,
				'Ip' => array(
						'City' => array(
								'fields' => array(
										'City.name',
								)
						) ,
						'State' => array(
								'fields' => array(
										'State.name',
								)
						) ,
						'Country' => array(
								'fields' => array(
										'Country.name',
										'Country.iso_alpha2',
								)
						) ,
						'fields' => array(
								'Ip.ip',
								'Ip.latitude',
								'Ip.longitude',
								'Ip.host'
						)
				) ,
		);
		if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstPledgeProjectStatus::PendingAction) {
			$conditions['Project.is_pending_action_to_admin'] = 1;
			unset($conditions['Pledge.pledge_project_status_id']);
		}
		if (!empty($this->request->params['named']['step'])) {
			$admin_pending_projects = $this->Pledge->Project->find('all', array(
					'conditions' => $conditions,
					'recursive' => -1
			));
			$projectIds = array();
			foreach($admin_pending_projects as $admin_project) {
				if (max(array_keys(unserialize($admin_project['Project']['tracked_steps']))) == $this->request->params['named']['step']) {
					$projectIds[] = $admin_project['Project']['id'];
				}
			}
			$conditions['Project.id'] = $projectIds;
		}
		if (!empty($this->request->data['Project']['filter_id']) && $this->request->data['Project']['filter_id'] != ConstMoreAction::Drafted) {
			$conditions['Project.is_draft'] = 0;
		}
		$this->paginate = array(
				'conditions' => $conditions,
				'contain' => $contain,
				'order' => array(
						'Project.id' => 'desc'
				) ,
				'recursive' => 3
		);
		/// Status Based on Count
		$this->set('opened_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForFunding,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('idea_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForIdea,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('pending_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::Pending,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('canceled_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::ProjectCanceled,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('allow_overfunding', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.is_allow_over_funding = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('goal_reached', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id =' => ConstPledgeProjectStatus::GoalReached,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('closed_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('open_for_idea', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForIdea,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('expired_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::FundingExpired,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('paid_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.project_type_id' => ConstProjectTypes::Pledge,
						'Project.is_paid' => 1
				) ,
				'recursive' => 0
		)));
		// total openid users list
		$this->set('suspended', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_admin_suspended = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('user_flagged', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_user_flagged = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('system_flagged', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_system_flagged = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('system_drafted', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_draft = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('successful_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_successful = ' => 0,
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('failed_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_successful = ' => 1,
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('active_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_active' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('inactive_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_active' => 0,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('featured_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_featured' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('total_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('total_flexible_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.payment_method_id' => ConstPaymentMethod::KiA,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('total_fixed_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.payment_method_id' => ConstPaymentMethod::AoN,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('projects', $this->paginate('Project'));
		$filters = $this->Pledge->Project->isFilterOptions;
		$moreActions = $this->Pledge->Project->moreActions;
		if (empty($this->request->data['Project']['project_status_id']) || $this->request->data['Project']['project_status_id'] != ConstPledgeProjectStatus::FundingClosed) {
			unset($moreActions[ConstMoreAction::Successful]);
			unset($moreActions[ConstMoreAction::Failed]);
		}
		$projectStatuses = $this->Pledge->PledgeProjectStatus->find('list', array(
				'conditions' => array(
						'PledgeProjectStatus.is_active' => 1
				) ,
				'recursive' => -1
		));
		$this->set('moreActions', $moreActions);
		$this->set('filters', $filters);
		$this->set('projectStatuses', $projectStatuses);
		if (!empty($this->request->data['Project']['project_status_id']) && $this->request->data['Project']['project_status_id'] == ConstPledgeProjectStatus::PendingAction) {
			$this->set('step_count', $this->Pledge->Project->getStepCount(ConstProjectTypes::Pledge));
			$this->render('admin_index_pending');
		}
	}
	public function admin_pledge_svg()
	{
		$this->loadModel('Projects.FormFieldStep');
		$formFieldStep = $this->FormFieldStep->find('count', array(
				'conditions' => array(
						'FormFieldStep.is_splash' => 1,
						'FormFieldStep.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		));
		$this->set('formFieldStep', $formFieldStep);
		/// Status Based on Count
		$this->set('opened_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForFunding,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('pending_action_to_admin_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_pending_action_to_admin' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge,
						'Project.is_draft' => 0,
							
				) ,
				'recursive' => -1
		)));
		$this->set('idea_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForIdea,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('pending_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::Pending,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('canceled_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::ProjectCanceled,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('allow_overfunding', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.is_allow_over_funding = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('goal_reached', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id =' => ConstPledgeProjectStatus::GoalReached,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('closed_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('open_for_idea', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::OpenForIdea,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('expired_project_count', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id = ' => ConstPledgeProjectStatus::FundingExpired,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('paid_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.project_type_id' => ConstProjectTypes::Pledge,
						'Project.is_paid' => 1
				) ,
				'recursive' => 0
		)));
		// total openid users list
		$this->set('suspended', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_admin_suspended = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('user_flagged', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_user_flagged = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('system_flagged', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_system_flagged = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('system_drafted', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_draft = ' => 1,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->set('successful_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_successful = ' => 1,
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('failed_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.is_successful = ' => 0,
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::FundingClosed,
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => 0
		)));
		$this->set('total_projects', $this->Pledge->Project->find('count', array(
				'conditions' => array(
						'Project.project_type_id' => ConstProjectTypes::Pledge
				) ,
				'recursive' => -1
		)));
		$this->layout = 'ajax';
	}
	public function admin_funds()
	{
		$this->_redirectPOST2Named(array(
				'q'
		));
		$this->loadModel('Projects.ProjectFund');
		$this->pageTitle = sprintf(__l('%s %s Funds') , Configure::read('project.alt_name_for_pledge_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps'));
		$conditions = array();
		$project_ids = $this->Pledge->find('list', array(
				'conditions' => array(
						'Pledge.pledge_project_status_id' => ConstPledgeProjectStatus::OpenForFunding
				) ,
				'fields' => array(
						'Pledge.project_id'
				) ,
				'recursive' => -1
		));
		if (!empty($this->request->params['named']['project_id'])) {
			$conditions['ProjectFund.project_id'] = $this->request->params['named']['project_id'];
		}
		if (!empty($this->request->params['named']['type'])) {
			if ($this->request->params['named']['type'] == Configure::read('project.project.alt_name_for_backer_past_tense')) {
				$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
				$this->pageTitle.= ' - ' . __l('Backed');
			} elseif ($this->request->params['named']['type'] == 'captured') {
				$conditions['NOT']['Project.id'] = $project_ids;
				$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
				$this->pageTitle.= ' - ' . __l('Captured');
			} elseif ($this->request->params['named']['type'] == 'authorized') {
				$conditions['Project.id'] = $project_ids;
				$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
				$this->pageTitle.= ' - ' . __l('Authorized');
			} elseif ($this->request->params['named']['type'] == 'failed') {
				$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaymentFailed;
				$this->pageTitle.= ' - ' . __l('Failed');
			} elseif ($this->request->params['named']['type'] == 'voided') {
				$conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Canceled;
				$this->pageTitle.= ' - ' . __l('Voided');
			} elseif ($this->request->params['named']['type'] == 'refunded') {
				$conditions['ProjectFund.project_fund_status_id'] = array(
						ConstProjectFundStatus::Expired,
						ConstProjectFundStatus::Canceled
				);
				$this->pageTitle.= ' - ' . __l('Refunded');
			}
		} else {
			$conditions['ProjectFund.project_fund_status_id'] = array(
					ConstProjectFundStatus::Authorized,
					ConstProjectFundStatus::PaidToOwner
			);
		}
		$conditions['ProjectFund.project_type_id'] = ConstProjectTypes::Pledge;
		if (!empty($this->request->params['named']['project'])) {
			$conditions['ProjectFund.project_id'] = $this->request->params['named']['project'];
			$project_name = $this->ProjectFund->Project->find('first', array(
					'conditions' => array(
							'Project.id' => $this->request->params['named']['project'],
					) ,
					'fields' => array(
							'Project.name',
					) ,
					'recursive' => -1,
			));
			$this->pageTitle.= ' - ' . $project_name['Project']['name'];
		}
		if (!empty($this->request->params['named']['project_id'])) {
			$conditions['ProjectFund.project_id'] = $this->request->params['named']['project_id'];
			$project_name = $this->ProjectFund->Project->find('first', array(
					'conditions' => array(
							'Project.id' => $this->request->params['named']['project_id'],
					) ,
					'fields' => array(
							'Project.name',
					) ,
					'recursive' => -1,
			));
			$this->pageTitle.= ' - ' . $project_name['Project']['name'];
		} elseif (!empty($this->request->params['named']['user_id'])) {
			$conditions['ProjectFund.user_id'] = $this->request->params['named']['user_id'];
			$user = $this->{$this->modelClass}->User->find('first', array(
					'conditions' => array(
							'User.id' => $this->request->params['named']['user_id']
					) ,
					'fields' => array(
							'User.id',
							'User.username'
					) ,
					'recursive' => -1
			));
			if (empty($user)) {
				throw new NotFoundException(__l('Invalid request'));
			}
			$this->pageTitle.= ' - ' . $user['User']['username'];
		}
		if (!empty($this->request->params['named']['q'])) {
			$conditions['AND']['OR'][]['User.username LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$conditions['AND']['OR'][]['Project.name LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$conditions['AND']['OR'][]['Project.description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$conditions['AND']['OR'][]['Project.short_description LIKE'] = '%' . $this->request->params['named']['q'] . '%';
			$this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
			$this->request->data['ProjectFund']['q'] = $this->request->params['named']['q'];
		}
		$this->set('authorized_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_id' => $project_ids,
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Authorized,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('failed_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaymentFailed,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('captured_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'NOT' => array(
								'ProjectFund.project_id' => $project_ids,
						) ,
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::PaidToOwner,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('voided_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Canceled,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('backed_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id' => ConstProjectFundStatus::Authorized,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('refunded_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id' => array(
								ConstProjectFundStatus::Expired,
								ConstProjectFundStatus::Canceled,
						) ,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('paid_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id ' => ConstProjectFundStatus::PaidToOwner,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$this->set('fund_count', $this->ProjectFund->find('count', array(
				'conditions' => array(
						'ProjectFund.project_fund_status_id' => array(
								ConstProjectFundStatus::Authorized,
								ConstProjectFundStatus::PaidToOwner
						) ,
						'ProjectFund.project_type_id' => ConstProjectTypes::Pledge
				)
		)));
		$contain = array(
				'Project' => array(
						'Pledge' => array(
								'PledgeProjectStatus'
						)
				) ,
				'User',
		);
		if (isPluginEnabled('ProjectRewards')) {
			$contain['ProjectReward'] = array();
		}
		$this->paginate = array(
				'conditions' => $conditions,
				'contain' => $contain,
				'order' => array(
						'ProjectFund.id' => 'desc'
				) ,
				'recursive' => 3
		);
		$this->set('projectFunds', $this->paginate('ProjectFund'));
		$total_pledge_conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::Authorized;
		$pledge = $this->ProjectFund->find('first', array(
				'conditions' => $total_pledge_conditions,
				'fields' => array(
						'SUM(ProjectFund.amount) as total_amount',
				) ,
				'recursive' => -1
		));
		$total_pledge = ($pledge[0]['total_amount']) ? $pledge[0]['total_amount'] : 0;
		$total_paid_conditions['ProjectFund.project_fund_status_id'] = ConstProjectFundStatus::PaidToOwner;
		$paid = $this->ProjectFund->find('first', array(
				'conditions' => $total_paid_conditions,
				'fields' => array(
						'SUM(ProjectFund.amount) as total_amount',
				) ,
				'recursive' => -1
		));
		$total_paid = ($paid[0]['total_amount']) ? $paid[0]['total_amount'] : 0;
		$total_refunded_conditions['ProjectFund.project_fund_status_id'] = array(
				ConstProjectFundStatus::Expired,
				ConstProjectFundStatus::Canceled
		);
		$refunded = $this->ProjectFund->find('first', array(
				'conditions' => $total_refunded_conditions,
				'fields' => array(
						'SUM(ProjectFund.amount) as total_amount',
				) ,
				'recursive' => -1
		));
		$total_refunded = ($refunded[0]['total_amount']) ? $refunded[0]['total_amount'] : 0;
		$this->set(compact('projectStatuses'));
		$this->set('total_pledge', $total_pledge);
		$this->set('total_paid', $total_paid);
		$this->set('total_refunded', $total_refunded);
		if (!empty($this->request->params['named']['project_id'])) {
			$this->set("project_id", $this->request->params['named']['project_id']);
		}
	}
}
?>