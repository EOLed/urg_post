<?php
App::uses('Sanitize', "Utility");
App::uses('MarkdownHelper', 'Markdown.View/Helper');
App::uses('AppController', 'Controller');
/**
 * PostComments Controller
 *
 * @property PostComment $PostComment
 */
class PostCommentsController extends AppController {
    var $helpers = array("Time", "Html", "Form", "Markdown.Markdown");

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->PostComment->recursive = 0;
		$this->set('postComments', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->PostComment->id = $id;
		if (!$this->PostComment->exists()) {
			throw new NotFoundException(__('Invalid post comment'));
		}
		$this->set('postComment', $this->PostComment->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->PostComment->create();
            $logged_user = $this->Session->read("User");
            if ($logged_user != null) {
                $this->request->data["PostComment"]["user_id"] = $logged_user["User"]["id"];
            }
			if ($this->PostComment->save($this->request->data)) {
				$this->Session->setFlash(__('The post comment has been saved'));
				$this->redirect($this->referer());
			} else {
				$this->Session->setFlash(__('The post comment could not be saved. Please, try again.'));
			}
		}
		$posts = $this->PostComment->Post->find('list');
		$users = $this->PostComment->User->find('list');
		$this->set(compact('posts', 'users'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->PostComment->id = $id;
		if (!$this->PostComment->exists()) {
			throw new NotFoundException(__('Invalid post comment'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->PostComment->save($this->request->data)) {
				$this->Session->setFlash(__('The post comment has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The post comment could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->PostComment->read(null, $id);
		}
		$posts = $this->PostComment->Post->find('list');
		$users = $this->PostComment->User->find('list');
		$this->set(compact('posts', 'users'));
	}


    public function post($post_id) {
        $this->layout = false;
        $this->set("comments", 
                   $this->PostComment->find("all", 
                                            array("conditions" => array("Post.id" => $post_id),
                                                  "order" => "PostComment.created")));
    }

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->PostComment->id = $id;
		if (!$this->PostComment->exists()) {
			throw new NotFoundException(__('Invalid post comment'));
		}
		if ($this->PostComment->delete()) {
			$this->Session->setFlash(__('Post comment deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Post comment was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
