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
class ConstPledgeProjectStatus
{
    const Pending = 1;
    const OpenForFunding = 2;
    const FundingClosed = 3;
    const FundingExpired = 4;
    const ProjectCanceled = 5;
    const GoalReached = 6;
    const OpenForIdea = 8;
    const PendingAction = 9;
}
?>