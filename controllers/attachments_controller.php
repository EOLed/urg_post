<?php
class AttachmentsController extends UrgPostAppController {

	var $name = 'Attachments';

	function index() {
		$this->Attachment->recursive = 0;
		$this->set('attachments', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('attachment', $this->Attachment->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Attachment->create();
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		}
		$attachmentTypes = $this->Attachment->AttachmentType->find('list');
		$posts = $this->Attachment->Post->find('list');
		$users = $this->Attachment->User->find('list');
		$this->set(compact('attachmentTypes', 'posts', 'users'));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid attachment', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Attachment->save($this->data)) {
				$this->Session->setFlash(__('The attachment has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The attachment could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Attachment->read(null, $id);
		}
		$attachmentTypes = $this->Attachment->AttachmentType->find('list');
		$posts = $this->Attachment->Post->find('list');
		$users = $this->Attachment->User->find('list');
		$this->set(compact('attachmentTypes', 'posts', 'users'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for attachment', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Attachment->delete($id)) {
			$this->Session->setFlash(__('Attachment deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Attachment was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>
