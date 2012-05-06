<?php
App::uses("UrgPostAppModel", "UrgPost.Model");
class Post extends UrgPostAppModel {
	var $name = 'Post';
	var $displayField = 'title';
    //var $actsAs = array("UrgSubscription.NotifySubscribers");

	var $validate = array(
		'group_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'title' => array(
			'notEmpty' => array(
				'rule' => 'notEmpty',
                'message' => 'Please enter a title',
                'required' => true,
                'allowEmpty' => false
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	var $belongsTo = array(
		'Group' => array(
			'className' => 'Urg.Group',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'Urg.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

    var $hasMany = array("Attachment");
}
?>
