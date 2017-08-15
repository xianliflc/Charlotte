<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/15/17
 * Time: 11:24 AM
 */

function routes () {

    return array(

        'time'   =>  array(
            'path'    =>  'Timestamp',
            'controller'    => 'Time',
            'package'  =>  'Time',
            'options' => array()
        ),

        'transaction' => array(
            'path'    =>  'Transaction',
            'controller'    => 'Transaction',
            'package'  =>  'Transaction',
            'options' => array()
        ),

        'transaction_status' => array(
            'path'    =>  'TransactionStats',
            'controller'    => 'Transaction',
            'action'  =>  'status',
            'package'  =>  'Transaction',
            'options' => array()
        ),

        'score_post' => array(
            'path'    =>  'ScorePost',
            'controller'    => 'Score',
            'action'  =>  'post',
            'package'  =>  'Score',
            'options' => array()
        ),

        'leaderboard_get' => array(
            'path'    =>  'LeaderboardGet',
            'controller'    => 'Score',
            'action'  =>  'getLeaderboard',
            'package'  =>  'Score',
            'options' => array()
        ),

        'user_save' => array(
            'path'    =>  'UserSave',
            'controller'    => 'User',
            'action'  =>  'save',
            'package'  =>  'User',
            'options' => array()
        ),

        'user_load' => array(
            'path'    =>  'UserLoad',
            'controller'    => 'User',
            'action'  =>  'load',
            'package'  =>  'User',
            'options' => array()
        )

    );

}
