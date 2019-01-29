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
require_once 'constants.php';
CmsNav::add('Projects', array(
    'title' => 'Projects',
    'url' => array(
        'controller' => 'projects',
        'action' => 'index',
    ) ,
    'data-bootstro-step' => "4",
    'data-bootstro-content' => __l("To monitor the summary, price point statistics of site and also to manage all projects posted in the site.") ,
    'weight' => 30,
    'icon-class' => 'file',
    'children' => array(
        'Pledge Projects' => array(
            'title' => Configure::read('project.alt_name_for_pledge_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps') ,
            'url' => array(
                'controller' => 'pledges',
                'action' => 'index'
            ) ,
            'weight' => 40,
        ) ,
    ) ,
));
CmsNav::add('masters', array(
    'title' => 'Masters',
    'weight' => 200,
    'children' => array(
        'Pledge Projects' => array(
            'title' => Configure::read('project.alt_name_for_pledge_singular_caps') . ' ' . Configure::read('project.alt_name_for_project_plural_caps') ,
            'url' => '',
            'weight' => 500,
        ) ,
        'Pledge Project Categories' => array(
            'title' => sprintf(__l('%s %s Categories') , Configure::read('project.alt_name_for_pledge_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'pledge_project_categories',
                'action' => 'index',
            ) ,
            'weight' => 510,
        ) ,
        'Pledge Project Statuses' => array(
            'title' => sprintf(__l('%s %s Statuses') , Configure::read('project.alt_name_for_pledge_singular_caps') , Configure::read('project.alt_name_for_project_singular_caps')) ,
            'url' => array(
                'controller' => 'pledge_project_statuses',
                'action' => 'index',
            ) ,
            'weight' => 520,
        ) ,
    )
));
CmsNav::add('payments', array(
    'title' => __l('Payments') ,
    'weight' => 50,
    'children' => array(
        'Projects Funded' => array(
            'title' => __l('Projects Funded') ,
            'url' => '',
            'weight' => 300,
        ) ,
        'Pledge Project Funds' => array(
            'title' => sprintf(__l('%s') , Configure::read('project.alt_name_for_pledge_plural_caps')) ,
            'url' => array(
                'controller' => 'pledges',
                'action' => 'funds',
            ) ,
            'weight' => 310,
        ) ,
    )
));
$defaultModel = array(
    'Project' => array(
        'hasOne' => array(
            'Pledge' => array(
                'className' => 'Pledge.Pledge',
                'foreignKey' => 'project_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
    'ProjectFund' => array(
        'hasOne' => array(
            'PledgeFund' => array(
                'className' => 'Pledge.PledgeFund',
                'foreignKey' => 'project_fund_id',
                'dependent' => true,
                'conditions' => '',
                'fields' => '',
                'order' => '',
                'limit' => '',
                'offset' => '',
                'exclusive' => '',
                'finderQuery' => '',
                'counterQuery' => ''
            ) ,
        ) ,
    ) ,
);
CmsHook::bindModel($defaultModel);
