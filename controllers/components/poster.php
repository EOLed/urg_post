<?php
App::import("Component", "ImgLib.ImgLib");
App::import("Component", "Cuploadify.Cuploadify");
class PosterComponent extends Object {
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";

    var $AUDIO = "/app/plugins/urg_post/webroot/audio";
    var $IMAGES = "/app/plugins/urg_post/webroot/img";
    var $FILES = "/app/plugins/urg_post/webroot/files";

    var $BANNER_SIZE = 700;
    var $PANEL_BANNER_SIZE = 460;

    var $components = array("Auth", "ImgLib", "Cuploadify");

    var $controller = null;

    function initialize(&$controller, $settings=array()) {
        $this->controller =& $controller;
    }
    
    function consolidate_attachments($webroot_dirs, $temp_dir) {
        $doc_root = $this->remove_trailing_slash(env("DOCUMENT_ROOT"));

        if (!is_array($webroot_dirs)) {
            $webroot_dirs = array($webroot_dirs);
        }

        foreach ($webroot_dirs as $webroot_dir) {
            $temp_webroot = "$webroot_dir/$temp_dir";

            if (file_exists($doc_root . $temp_webroot)) {
                $perm_dir = $webroot_dir . "/" . $this->controller->Post->id;
                $this->rename_dir($doc_root . $temp_webroot, $doc_root . $perm_dir);
                $this->log("moved attachments to permanent folder: $doc_root$perm_dir", LOG_DEBUG);
            } else {
                $this->log("no attachments to move, since folder doesn't exist: $doc_root$temp_webroot",
                        LOG_DEBUG);
            }
        }
    }

    /**
     * Renames the directory, even if there are contents inside of it.
     * @param $string old_dir The old directory name.
     * @param $string new_dir The new directory name.
     */
    function rename_dir($old_name, $new_name) {
        $this->log("Moving $old_name to $new_name", LOG_DEBUG);
        if (file_exists($old_name)) {
            $this->log("creating dir: $new_name", LOG_DEBUG);
            $old = umask(0);
            mkdir($new_name, 0777, true); 
            umask($old);
            if ($handle = opendir($old_name)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                       rename("$old_name/$file", "$new_name/$file"); 
                    }
                }
                closedir($handle);
                rmdir($old_name);
            }
        }
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
    
    function get_doc_root($root = null) {
        $doc_root = $this->remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
    }
    
	/**
	 * Function used to delete a folder.
	 * @param $path full-path to folder
	 * @return bool result of deletion
	 */
	function rrmdir($path) {
	    if (is_dir($path)) {
		    if (version_compare(PHP_VERSION, '5.0.0') < 0) {
			    $entries = array();
			    if ($handle = opendir($path)) {
			        while (false !== ($file = readdir($handle))) $entries[] = $file;
			        closedir($handle);
			    }
            } else {
			    $entries = scandir($path);
			    if ($entries === false) $entries = array();
		    }
	
		    foreach ($entries as $entry) {
		        if ($entry != '.' && $entry != '..') {
			        $this->rrmdir($path.'/'.$entry);
		        }
		    }
	
		    return rmdir($path);
	    } else {
		    return unlink($path);
	    }
	}

    function resize_banner($post_id) {
        $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .  $post_id;

        if (file_exists($full_image_path)) {
            $this->controller->loadModel("Attachment");
            $this->controller->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

            $banner_type = $this->controller->Attachment->AttachmentType->findByName("Banner");
            $post_banner = $this->controller->Attachment->find("first", 
                    array("conditions" => array("AND" => array(
                    "Attachment.attachment_type_id" => $banner_type["AttachmentType"]["id"],
                    "Attachment.post_id" => $post_id 
            ))));

            if (isset($post_banner["Attachment"])) {
                $this->log("post banner: " . Debugger::exportVar($post_banner, 3), LOG_DEBUG);
                $this->log("resizing banners...", LOG_DEBUG);
                $this->log("full post image path: $full_image_path", LOG_DEBUG);
                $saved_image = $this->ImgLib->get_image($full_image_path . "/" . 
                        $post_banner["Attachment"]["filename"], $this->BANNER_SIZE, 0, 'landscape');
                $this->log("saved $saved_image[filename]", LOG_DEBUG);
            } else {
                $this->log("no banners found for post: " . $post_id, LOG_DEBUG);
            }
        }
    }

    function delete_attachment($id) {
        $dom_id = $this->controller->params["url"]["domId"];
        $success = $this->controller->Post->Attachment->delete($id);
        $this->controller->set("data", array("success"=>$success === true, "domId"=>$dom_id));
        $this->controller->render("json", "ajax");
    }

    function prepare_attachments(&$data) {
        $logged_user = $this->Auth->user();
        $attachment_count = isset($data["Attachment"]) ? 
                sizeof($data["Attachment"]) : 0;
        if ($attachment_count > 0) {
            $this->log("preparing $attachment_count attachments...", LOG_DEBUG);
            foreach ($data["Attachment"] as &$attachment) {
                $attachment["user_id"] = $logged_user["User"]["id"];
            }

            $this->controller->Post->bindModel(array("hasMany" => array("Attachment")));
            unset($this->controller->Post->Attachment->validate["post_id"]);
        }
    }

    function get_image_path($filename, $post, $width, $height = 0) {
        $full_image_path = $this->get_doc_root($this->IMAGES) . "/" .  $post["Post"]["id"];
        $image = $this->ImgLib->get_image("$full_image_path/$filename", $width, $height, 'landscape'); 
        return "/urg_post/img/" . $post["Post"]["id"] . "/" . $image["filename"];
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

    function upload_attachments() {
        $this->log("uploading attachments...", LOG_DEBUG);

        $this->log("determining what type of attachment...", LOG_DEBUG);

        $this->controller->loadModel("Attachment");
        $this->controller->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));
        $attachment_type = null;
        $root = null;
        if ($this->is_filetype($this->Cuploadify->get_filename(),
                array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $root = $this->IMAGES;
            $attachment_type = $this->Attachment->AttachmentType->findByName("Images");
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->is_filetype($this->Cuploadify->get_filename(), array(".mp3"))) {
            $root = $this->AUDIO;
            $attachment_type = $this->controller->Attachment->AttachmentType->findByName("Audio");
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->is_filetype($this->Cuploadify->get_filename(), 
                array(".ppt", ".pptx", ".doc", ".docx"))) {
            $root = $this->FILES;
            $attachment_type = $this->controller->Attachment->AttachmentType->findByName("Documents");
            $webroot_folder = $this->FILES_WEBROOT;
        }

        $webroot_folder = $this->get_webroot_folder($this->Cuploadify->get_filename());
        $this->log("attachment type detected as: " . Debugger::exportVar($attachment_type, 3), 
                LOG_DEBUG);
        $this->upload($root);

        //TODO cache id
        $this->controller->set("data", array(
                "attachment_type_id"=>$attachment_type["AttachmentType"]["id"],
                "webroot_folder"=>$webroot_folder
        ));
    }

    function upload($root) {
        $options = array("root" => $root);
        $this->log("uploading options: " . Debugger::exportVar($options), LOG_DEBUG);
        $this->Cuploadify->upload($options);
        $this->log("done uploading.", LOG_DEBUG);
    }
}
