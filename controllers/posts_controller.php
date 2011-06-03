<?php
App::import("Component", "UrgPost.Poster");
App::import("Component", "Cuploadify.Cuploadify");
App::import("Component", "ImgLib.ImgLib");
App::import("Helper", "UrgPost.Post");
App::import("Component", "UrgSubscription.NotifySubscribers");
class PostsController extends UrgPostAppController {
	var $name = 'Posts';

    var $IMAGES = "/app/plugins/urg_post/webroot/img";
    var $components = array(
           "Auth" => array(
                   "loginAction" => array(
                           "plugin" => "urg",
                           "controller" => "users",
                           "action" => "login",
                           "admin" => false
                   )
           ), "Urg", "Poster", "Cuploadify", "ImgLib", "NotifySubscribers"
    );

    var $helpers = array("Post");

	function index() {
		$this->Post->recursive = 0;
		$this->set('posts', $this->paginate());
	}

	function view($id = null, $slug = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid post', true));
			$this->redirect(array('action' => 'index'));
		}

        $post = $this->Post->read(null, $id);

        if (!$slug || $slug != $post["Post"]["slug"]) {
            if (isset($post["Post"]["slug"]) && $post["Post"]["slug"] != "") {
                $slug = $post["Post"]["slug"];
            } else {
                $this->Post->id = $id;
                $slug = strtolower(Inflector::slug($post["Post"]["title"], "-"));
                $this->Post->saveField("slug", $slug);
            }
            $this->redirect("/urg_post/posts/view/$id/$slug");
        }

        $this->log("Viewing post: " . Debugger::exportVar($post, 3), LOG_DEBUG);
		$this->set('post', $post);
        $group = $this->Post->Group->findById($post["Group"]["id"]);
        $this->set("upcoming_events", $this->get_upcoming_activity($group));
        $about = $this->get_about("Montreal Chinese Alliance Church");
        $about_group = $this->get_about($group["Group"]["name"]);
        $this->set("about", $about);

        $banners = $this->get_banners($post);
        if (empty($banners)) {
            $banners = $this->get_banners($about_group);
            if (empty($banners)) {
                $banners = $this->get_banners($about);
            }
        }

        $this->set("title_for_layout", $post["Group"]["name"] . " &raquo; " . $post["Post"]["title"]);

        $this->set("banners", $banners);
	}

    function view_group($slug) {
        if (!$slug) {
            $this->Session->setFlash(__('Must specify group.', true));
            $this->redirect(array('action' => 'index'));
        }
        $group = $this->Post->Group->findBySlug($slug);

        $this->log("Viewing group: " . Debugger::exportVar($group, 3), LOG_DEBUG);
        $about_group = $this->get_about($group["Group"]["name"]);
        $about = $this->get_about("Montreal Chinese Alliance Church");
        $this->set('group', $group);
        $this->set("about_group", $about_group);
        $this->set("activity", $this->get_recent_activity($group));
        $this->set("upcoming_events", $this->get_upcoming_activity($group));
        $this->set("about", $about);

        $banners = $this->get_banners($about_group);
        if (empty($banners)) {
            $banners = $this->get_banners($about);
        }

        $this->set("title_for_layout", __("Groups", true) . " &raquo; " . $group["Group"]["name"]);

        $this->set("banners", $banners);
    }

	function add($group_slug = null) {
		if (!empty($this->data)) {
			$this->Post->create();
            $post_creator = $this->Auth->user();
            $this->data["User"] = $post_creator["User"];
            $this->log("new post created by: " . Debugger::exportVar($post_creator["User"]), LOG_DEBUG);
            $this->Poster->prepare_attachments($this->data);
            $this->log("saving post: " . Debugger::exportVar($this->data, 3), LOG_DEBUG);
			if ($this->Post->saveAll($this->data, array("atomic" => false))) {
                $temp_dir = $this->data["Post"]["uuid"];

                $this->Poster->consolidate_attachments(
                        array($this->Poster->AUDIO, $this->Poster->FILES, $this->Poster->IMAGES), 
                        $temp_dir
                );

                $this->Poster->resize_banner($this->Post->id);

                $this->data["Post"]["id"] = $this->Post->id;

                $this->NotifySubscribers->execute();
                
				$this->Session->setFlash(__('The post has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The post could not be saved. Please, try again.', true));
			}
		} else {
            $this->data["Post"]["uuid"] = String::uuid();
        }

        if ($group_slug != null) {
            $group = $this->Post->Group->findBySlug($group_slug);
            $this->log("group id: " . $group["Group"]["id"], LOG_DEBUG);
            $this->data["Post"]["group_id"] = $group["Group"]["id"];
        }

        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $this->set("banner_type", 
                $this->Attachment->AttachmentType->findByName("Banner"));
        $this->set("audio_type", 
                $this->Attachment->AttachmentType->findByName("Audio"));
		$groups = $this->Post->Group->find('list');
		$this->set(compact('groups'));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid post', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Post->save($this->data)) {
				$this->Session->setFlash(__('The post has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The post could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Post->read(null, $id);
		}
		$groups = $this->Post->Group->find('list');
		$users = $this->Post->User->find('list');
		$this->set(compact('groups', 'users'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for post', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Post->delete($id)) {
			$this->Session->setFlash(__('Post deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Post was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}

    /**
     * Validates the field specified by the parameters.
     * Returns the error message key.
     */
    function validate_field($model_name="Post", $field) {
        $this->layout = "ajax";
        $errors = array();

        $this->data[$model_name][$field] = $this->params["url"]["value"];

        $model = $model_name == "Post" ? $this->Post : $this->Post->{$model_name};
        $model->set($this->data);

        if ($model->validates(array("fieldList"=>array($field)))) {
        } else {
            $errors = $model->invalidFields();
        }

        $this->log("Errors on $model_name.$field: " . Debugger::exportVar($errors, 2), LOG_DEBUG);
        $this->set("error", isset($errors[$field]) ? $errors[$field] : null);
        $this->set("model", $model_name);
        $this->set("field", $field);
    }

    function upload($root) {
        $this->Poster->upload($root);
    }

    function upload_images() {
        $this->log("uploading images...", LOG_DEBUG);
        $this->upload($this->Poster->IMAGES);
    }

    function upload_attachments() {
        $this->Poster->upload_attachments();
        $this->render("json", "ajax");
    }

    function upload_image() {
        $this->upload($this->Poster->IMAGES);
//        $options = array("root" => $this->Poster->IMAGES);
//        $target_folder = $this->Cuploadify->get_target_folder($options);
//        $filename = $target_folder . $this->Cuploadify->get_filename();
    }

    function get_banners($post) {
        $this->loadModel("Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");

        $banners = array();

        if (isset($post["Attachment"])) {
            Configure::load("config");
            foreach ($post["Attachment"] as $attachment) {
                if ($attachment["attachment_type_id"] == $banner_type["AttachmentType"]["id"]) {
                    $this->log("getting banner for " . $attachment["filename"], LOG_DEBUG);
                    array_push($banners, $this->get_image_path($attachment["filename"],
                                                               $post,
                                                               Configure::read("Banner.defaultWidth")));
                }
            }
        }

        return $banners;
    }

    function get_about($name) {
        //$this->Post->bindModel(array("belongsTo" => array("Group")));
        $this->Post->bindModel(array("hasMany" => array("Attachment")));

        $about_group = $this->Post->Group->findByName("About");

        $about = $this->Post->find("first", 
                array("conditions" => 
                        array("OR" => array(
                                "Group.name" => "About", 
                                "Group.parent_id" => $about_group["Group"]["id"]),
                              "AND" => array("Post.title" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
        );

        $this->log("about for group: $name" .  Debugger::exportVar($about, 3), LOG_DEBUG);

        return $about;
    }

    function get_recent_activity($group) {
        $posts = $this->Post->find('all', 
                array("conditions" => array("Post.group_id" => $group["Group"]["id"],
                                            "Post.publish_timestamp < NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp DESC"));
        $activity = array();
        foreach ($posts as $post) {
            array_push($activity, $post);
        }
        
        $this->log("group activity: " . Debugger::exportVar($activity, 3), LOG_DEBUG);

        return $activity;
    }

    function get_upcoming_activity($group) {
        $posts = $this->Post->find('all', 
                array("conditions" => array("Post.group_id" => $group["Group"]["id"],
                                            "Post.publish_timestamp > NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp"));
        
        $this->log("upcoming posts: " . Debugger::exportVar($posts, 3), LOG_DEBUG);

        return $posts;
    }

    function get_image_path($filename, $post, $width, $height = 0) {
        $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .  $post["Post"]["id"];
        $image = $this->ImgLib->get_image("$full_image_path/$filename", $width, $height, 'landscape'); 
        return "/urg_post/img/" . $post["Post"]["id"] . "/" . $image["filename"];
    }

    function get_doc_root($root = null) {
        $doc_root = $this->remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
    }

    /**
     * Removes the trailing slash from the string specified.
     * @param $string the string to remove the trailing slash from.
     */
    function remove_trailing_slash($string) {
        $string_length = strlen($string);
        if (strrpos($string, "/") === $string_length - 1) {
            $string = substr($string, 0, $string_length - 1);
        }

        return $string;
    }

    function get_webroot_folder($filename) {
        $webroot_folder = null;

        if ($this->is_filetype($filename, array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->is_filetype($filename, array(".mp3"))) {
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->is_filetype($filename, array(".ppt", ".pptx", ".doc", ".docx"))) {
            $webroot_folder = $this->FILES_WEBROOT;
        }

        return $webroot_folder;
    }

    function is_filetype($filename, $filetypes) {
        $filename = strtolower($filename);
        $is = false;
        if (is_array($filetypes)) {
            foreach ($filetypes as $filetype) {
                if ($this->ends_with($filename, $filetype)) {
                    $is = true;
                    break;
                }
            }
        } else {
            $is = $this->ends_with($filename, $filetypes);
        }

        $this->log("is $filename part of " . implode(",",$filetypes) . "? " . ($is ? "true" : "false"), 
                LOG_DEBUG);
        return $is;
    }

    function ends_with($haystack, $needle) {
        return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
    }
}
?>
