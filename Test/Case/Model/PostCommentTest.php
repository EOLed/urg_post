<?php
App::uses('PostComment', 'Model');

/**
 * PostComment Test Case
 *
 */
class PostCommentTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.post_comment', 'app.post', 'app.user');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PostComment = ClassRegistry::init('PostComment');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PostComment);

		parent::tearDown();
	}

}
