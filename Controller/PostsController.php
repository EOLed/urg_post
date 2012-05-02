<?php
App::uses("PosterComponent", "UrgPost.Controller/Component");
App::uses("CuploadifyCompoment", "Cuploadify.Controller/Component");
App::uses("ImgLibComponent", "ImgLib.Controller/Component");
App::uses("FlyLoaderComponent", "Controller/Component");
App::uses("PostHelper", "UrgPost.View/Helper");
App::uses("MarkdownHelper", "Markdown.View/Helper");
App::uses("WidgetUtilComponent", "Urg.Controller/Component");
App::uses("UrgPostAppController", "UrgPost.Controller");
App::uses("Sanitize", "Utility");
App::uses("UrgComponent", "Urg.Controller/Component");
class PostsController extends UrgPostAppController {
	var $name = 'Posts';
    var $AUDIO_WEBROOT = "audio";
    var $IMAGES_WEBROOT = "img";
    var $FILES_WEBROOT = "files";
    var $AUDIO = "/app/Plugin/UrgPost/webroot/audio";
    var $IMAGES = "/app/Plugin/UrgPost/webroot/img";
    var $FILES = "/app/Plugin/UrgPost/webroot/files";
    var $WEBROOT = "/app/Plugin/UrgPost/webroot";

    var $BANNER_SIZE = 700;
    var $PANEL_BANNER_SIZE = 460;
    var $components = array(
           "Urg.Urg", "UrgPost.Poster", "Cuploadify.Cuploadify", "ImgLib.ImgLib", "Urg.WidgetUtil", "FlyLoader"
    );

    var $helpers = array("UrgPost.Post", "Markdown.Markdown", "Html", "Form", "Session");

	function index() {
		$this->Post->recursive = 0;
		$this->set('posts', $this->paginate());
	}

    function delete_attachment($id) {
        $dom_id = $this->params["url"]["domId"];
        $success = $this->Post->Attachment->delete($id);
        $this->set("data", array("success"=>$success === true, "domId"=>$dom_id));
        $this->render("json", "ajax");
    }

	function view($id = null, $slug = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid post'));
			$this->redirect(array('action' => 'index'));
		}

        $logged_user = $this->Session->read("User");

        $post = $this->Post->find("first", array("conditions" => array("Post.id" => $id)));

        $this->log("Viewing post ($id): " . Debugger::exportVar($post, 3), LOG_DEBUG);

        if (!$slug || $slug != $post["Post"]["slug"]) {
            if (isset($post["Post"]["slug"]) && $post["Post"]["slug"] != "") {
                $slug = $post["Post"]["slug"];
            } else {
                $this->Post->id = $id;
                $slug = strtolower(Inflector::slug(str_replace("'", "", $post["Post"]["title"]), "-"));
                $this->log("Post to create slug for ($id): " . Debugger::exportVar($post, 3), LOG_DEBUG);
                $this->log("Saving slug as: " . $slug, LOG_DEBUG);
                $this->Post->saveField("slug", $slug);
            }
            $this->redirect(array("plugin" => "urg_post",
                                  "controller" => "posts",
                                  "action" => "view",
                                  "lang" => $this->Session->read("Config.lang"),
                                  $id,
                                  $slug));
        }

        $this->log("Viewing post: " . Debugger::exportVar($post, 3), LOG_DEBUG);

        $this->set("widgets", $this->__prepare_widgets(
                $this->WidgetUtil->load($post["Group"]["id"],
                                        array("post_id" => $post["Post"]["id"],
                                              "group_id" => $post["Group"]["id"]))));

		$this->set('post', $post);
        $group = $this->Post->Group->findById($post["Group"]["id"]);

        $this->set("title_for_layout", $group["Group"]["name"] . " &raquo; " . $post["Post"]["title"]);
	}

    function __prepare_widgets($widgets) {
        $widget_list = array();
        foreach ($widgets as $widget) {
            if (strpos($widget["Widget"]["placement"], "|") === false) {
                $widget_list[$widget["Widget"]["placement"]] = $widget;
            } else {
                $placement = explode("|", $widget["Widget"]["placement"]);
                $widget_list[$placement[0]][$placement[1]] = $widget;
            }
        }

        $this->log("prepared widgets: " . Debugger::exportVar($widget_list, 3), LOG_DEBUG);

        return $widget_list;
    }

	function add($group_slug = null) {
        $post_creator = $this->Session->read("User");

		if (!empty($this->request->data)) {
			$this->Post->create();
            $this->request->data["User"] = $post_creator["User"];
            $this->log("new post created by: " . Debugger::exportVar($post_creator["User"]), LOG_DEBUG);
            $this->Poster->prepare_attachments($this->request->data);
            /*$post_timestamp = date_parse_from_format("F d, Y h:i A", 
                                                     $this->request->data["Post"]["displayDate"] . " " . 
                                                     $this->request->data["Post"]["displayTime"]);*/
            $post_timestamp = date_parse_from_format("Y-m-d h:i A", 
                                                     $this->request->data["Post"]["formatted_date"] . " " . 
                                                     $this->request->data["Post"]["displayTime"]);

            $this->request->data["Post"]["publish_timestamp"] = 
                    "$post_timestamp[year]-$post_timestamp[month]-$post_timestamp[day]" . 
                    " $post_timestamp[hour]:$post_timestamp[minute]";

            if (!isset($this->request->data["Post"]["slug"]) || strlen($this->data["Post"]["slug"]) == 0) {
                $this->request->data["Post"]["slug"] = strtolower(Inflector::slug(str_replace("'", "", $this->data["Post"]["title"]),
                                                                                  "-"));
            }

            //$this->request->data["Post"]["content"] = Sanitize::html($this->data["Post"]["content"]);
           /* $attachments = array();
            if (isset($this->request->data["Attachment"])) {
                $attachments = $this->request->data["Attachment"];
                unset($this->request->data["Attachment"]);
            }*/

            $this->log("now saving post: " . Debugger::exportVar($this->request->data, 3), LOG_DEBUG);
			if ($this->Post->saveAll($this->request->data)) {
                $vars = array("post_id" => $this->request->data["Post"]["id"], "group_id" => $this->data["Post"]["group_id"]);
                $widgets = $this->__prepare_widgets($this->WidgetUtil->load($this->request->data["Post"]["group_id"], $vars));
                $this->set("widgets", $widgets);
              /*  $this->loadModel("UrgPost.Attachment");
                foreach ($attachments as $attachment) {
                    $this->Attachment->create();
                    $this->Attachment->save(array("Attachment"=>$attachment));
                    $attachment_data["AttachmentMetadatum"] = array();
                    $attachment_data["AttachmentMetadatum"]["key"] = "post_id";
                    $attachment_data["AttachmentMetadatum"]["value"] = $this->request->data["Post"]["id"];
                    $attachment_data["AttachmentMetadatum"]["attachment_id"] = $this->Attachment->id;
                    $this->log("now saving attachments: " . Debugger::exportVar($attachment_data, 3), LOG_DEBUG);
                    $this->loadModel("UrgPost.AttachmentMetadatum");
                    $this->AttachmentMetadatum->save($attachment_data);
                }*/
                $this->Poster->resize_banner($this->request->data["Post"]["id"]);

                foreach ($widgets as $widget) {
                    if ($widget["Widget"]["placement"] == "backend") {
                        $component = $this->FlyLoader->get_name($widget["Widget"]["name"]);
                        $this->{$component}->execute();
                    }
                }
                
				$this->Session->setFlash(__('The post has been saved'));
				$this->redirect(array('action' => 'view', $this->request->data["Post"]["id"], $this->data["Post"]["slug"]));
			} else {
				$this->Session->setFlash(__('The post could not be saved. Please, try again.'));
			}
		} else {
            $this->loadModel("Urg.SequenceId");
            $this->request->data["Post"]["id"] = $this->SequenceId->next($this->Post->useTable);
            $this->request->data["Post"]["displayDate"] = date("F d, Y");
            $this->request->data["Post"]["displayTime"] = date("h:i A");
            $this->request->data["Post"]["formatted_date"] = date("Y-m-d");
            $this->log("next id for " . $this->Post->useTable . " " . $this->request->data["Post"]["id"], LOG_DEBUG);
            $this->log("post creator: " . Debugger::exportVar($post_creator, 3), LOG_DEBUG);
            $this->loadModel("Profile");
            $profile = $this->Profile->findByUserId($post_creator["User"]["id"]);
        }

        $group = null;
        if ($group_slug != null) {
            $group = $this->Post->Group->findBySlug($group_slug);
            $this->log("group id: " . $group["Group"]["id"], LOG_DEBUG);
            $this->request->data["Post"]["group_id"] = $group["Group"]["id"];
        }

        $this->__set_attachment_types();
		$groups = null;//$this->Post->Group->find("list");
        /*$group == null ? $this->Post->Group->find("list") :
                                   $this->Post->Group->children($group["Group"]["id"], false); */

        if ($group == null) {
            $all_groups = $this->Post->Group->find("all");
            $groups = $this->__build_groups_dropdown_list($all_groups);
        } else {
            $children = $this->Post->Group->children($group["Group"]["id"], false);
            $groups = $this->__build_groups_dropdown_list($children);
            $groups[$group["Group"]["id"]] = $group["Group"]["name"] . " (" . $group["Group"]["slug"] . ")";
        }
		$this->set(compact('groups'));
	}


    /** given a list of groups, return an array formatted to be displayed in dropdown */
    function __build_groups_dropdown_list($groups) {
        $dropdown_groups = array();
        foreach ($groups as $group) {
            $dropdown_groups[$group["Group"]["id"]] = $group["Group"]["name"] . " (" . $group["Group"]["slug"] . ")";
        }

        return $dropdown_groups;
    }

    function __set_attachment_types() {
        $this->loadModel("UrgPost.Attachment");
        $this->Attachment->bindModel(array("belongsTo" => array("AttachmentType")));

        $banner_type = $this->Attachment->AttachmentType->findByName("Banner");
        $this->set("banner_type", $banner_type);
        $this->set("audio_type", $this->Attachment->AttachmentType->findByName("Audio"));

        $banner = $this->Attachment->find("first", array(
                "conditions"=>
                        array("Attachment.post_id"=>$this->request->data["Post"]["id"],
                              "Attachment.attachment_type_id"=>$banner_type["AttachmentType"]["id"]
                        ),
                "order" => "Attachment.created DESC"
            )
        );

        if ($banner) {
            $this->set("banner", $this->__get_image_path($banner["Attachment"]["filename"], 
                                                       $this->request->data, 
                                                       $this->PANEL_BANNER_SIZE));
        }

        $this->set("attachments", $this->Attachment->find("all", array("conditions"=>
                array("Attachment.post_id"=>$this->request->data["Post"]["id"],
                      "Attachment.attachment_type_id !="=>$banner_type["AttachmentType"]["id"]
                )
            )
        ));
    }

	function edit($id = null) {
		if (!$id && empty($this->request->data)) {
			$this->Session->setFlash(__('Invalid post'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->request->data)) {
            $post_timestamp = date_parse_from_format("Y-m-d h:i A", 
                                                     $this->request->data["Post"]["formatted_date"] . " " . 
                                                     $this->request->data["Post"]["displayTime"]);
            $this->request->data["Post"]["publish_timestamp"] = 
                    "$post_timestamp[year]-$post_timestamp[month]-$post_timestamp[day]" . 
                    " $post_timestamp[hour]:$post_timestamp[minute]";
			if ($this->Post->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The post has been saved'));
                $referer = $this->Session->read("Referer");
                $this->Session->delete("Referer");
				$this->redirect($referer);
			} else {
				$this->Session->setFlash(__('The post could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Post->read(null, $id);
            CakeLog::write(LOG_DEBUG, "post to edit: " . Debugger::exportVar($this->request->data, 3));
            $this->request->data["Post"]["formatted_date"] = date("Y-m-d", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->request->data["Post"]["displayDate"] = date("F j, Y", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->request->data["Post"]["displayTime"] = date("h:i A", strtotime($this->data["Post"]["publish_timestamp"]));
            $this->Session->write("Referer", $this->referer());
		}
        $this->__set_attachment_types();

		$all_groups = $this->Post->Group->find('all');
        $groups = array();
        foreach ($all_groups as $group) {
            $groups[$group["Group"]["id"]] = $group["Group"]["name"] . " (" . $group["Group"]["slug"] . ")";
        }

		$users = $this->Post->User->find('list');
		$this->set(compact('groups', 'users'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for post'));
			$this->redirect(array('action'=>'index'));
		}

        $post = $this->Post->findById($id);
		if ($this->Post->delete($id)) {
			$this->Session->setFlash(__('Post deleted'));
			$this->redirect(array("plugin"=>"urg", 
                                  "controller"=>"groups",
                                  "action"=>"view",
                                  $post["Group"]["slug"]));
		}
		$this->Session->setFlash(__('Post was not deleted'));
        $this->redirect(array("plugin"=>"urg", "controller"=>"groups", "action"=>"view", $post["Group"]["slug"]));
	}

    /**
     * Validates the field specified by the parameters.
     * Returns the error message key.
     */
    function validate_field($model_name="Post", $field) {
        $this->layout = "ajax";
        $errors = array();

        $this->request->data[$model_name][$field] = $this->params["url"]["value"];

        $model = $model_name == "Post" ? $this->Post : $this->Post->{$model_name};
        $model->set($this->request->data);

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
        $this->layout = false;
//        $options = array("root" => $this->Poster->IMAGES);
//        $target_folder = $this->Cuploadify->get_target_folder($options);
//        $filename = $target_folder . $this->Cuploadify->get_filename();
    }

    function __get_image_path($filename, $post, $width, $height = 0) {
        $full_image_path = $this->__get_doc_root($this->IMAGES) . "/" .  $post["Post"]["id"];
        $image = $this->ImgLib->get_image("$full_image_path/$filename", $width, $height, 'landscape'); 
        return "/urg_post/img/" . $post["Post"]["id"] . "/" . $image["filename"];
    }

    function __get_doc_root($root = null) {
        $doc_root = $this->__remove_trailing_slash(env('DOCUMENT_ROOT'));

        if ($root != null) {
            $root = $this->__remove_trailing_slash($root);
            $doc_root .=  $root;
        }

        return $doc_root;
    }

    /**
     * Removes the trailing slash from the string specified.
     * @param $string the string to remove the trailing slash from.
     */
    function __remove_trailing_slash($string) {
        $string_length = strlen($string);
        if (strrpos($string, "/") === $string_length - 1) {
            $string = substr($string, 0, $string_length - 1);
        }

        return $string;
    }

    function get_webroot_folder($filename) {
        $webroot_folder = null;

        if ($this->__is_filetype($filename, array(".jpg", ".jpeg", ".png", ".gif", ".bmp"))) {
            $webroot_folder = $this->IMAGES_WEBROOT;
        } else if ($this->__is_filetype($filename, array(".mp3"))) {
            $webroot_folder = $this->AUDIO_WEBROOT;
        } else if ($this->__is_filetype($filename, array(".pdf", ".ppt", ".pptx", ".doc", ".docx"))) {
            $webroot_folder = $this->FILES_WEBROOT;
        }

        return $webroot_folder;
    }

    function __is_filetype($filename, $filetypes) {
        $filename = strtolower($filename);
        $is = false;
        if (is_array($filetypes)) {
            foreach ($filetypes as $filetype) {
                if ($this->__ends_with($filename, $filetype)) {
                    $is = true;
                    break;
                }
            }
        } else {
            $is = $this->__ends_with($filename, $filetypes);
        }

        $this->log("is $filename part of " . implode(",",$filetypes) . "? " . ($is ? "true" : "false"), 
                LOG_DEBUG);
        return $is;
    }

    function __ends_with($haystack, $needle) {
        return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
    }
}

if (!function_exists('date_parse_from_format')) {
  function date_parse_from_format($format, $date) {
    $i=0;
    $pos=0;
    $output=array();
    while ($i< strlen($format)) {
      $pat = substr($format, $i, 1);
      $i++;
      switch ($pat) {
        case 'd': //    Day of the month, 2 digits with leading zeros    01 to 31
          $output['day'] = substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'D': // A textual representation of a day: three letters    Mon through Sun
          //TODO
        break;
        case 'j': //    Day of the month without leading zeros    1 to 31
          $output['day'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['day']>31)) {
            $output['day'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'm': //    Numeric representation of a month: with leading zeros    01 through 12
          $output['month'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'n': //    Numeric representation of a month: without leading zeros    1 through 12
          $output['month'] = substr($date, $pos, 2);
          if (!is_numeric($output['month']) || ($output['month']>12)) {
            $output['month'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'Y': //    A full numeric representation of a year: 4 digits    Examples: 1999 or 2003
          $output['year'] = (int)substr($date, $pos, 4);
          $pos+=4;
        break;
        case 'y': //    A two digit representation of a year    Examples: 99 or 03
          $output['year'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'g': //    12-hour format of an hour without leading zeros    1 through 12
          $output['hour'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['hour']>12)) {
            $output['hour'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'G': //    24-hour format of an hour without leading zeros    0 through 23
          $output['hour'] = substr($date, $pos, 2);
          if (!is_numeric($output['day']) || ($output['hour']>23)) {
            $output['hour'] = substr($date, $pos, 1);
            $pos--;
          }
          $pos+=2;
        break;
        case 'h': //    12-hour format of an hour with leading zeros    01 through 12
          $output['hour'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'H': //    24-hour format of an hour with leading zeros    00 through 23
          $output['hour'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'i': //    Minutes with leading zeros    00 to 59
          $output['minute'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 's': //    Seconds: with leading zeros    00 through 59
          $output['second'] = (int)substr($date, $pos, 2);
          $pos+=2;
        break;
        case 'l': // (lowercase 'L')    A full textual representation of the day of the week    Sunday through Saturday
        case 'N': //    ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)    1 (for Monday) through 7 (for Sunday)
        case 'S': //    English ordinal suffix for the day of the month: 2 characters    st: nd: rd or th. Works well with j
        case 'w': //    Numeric representation of the day of the week    0 (for Sunday) through 6 (for Saturday)
        case 'z': //    The day of the year (starting from 0)    0 through 365
        case 'W': //    ISO-8601 week number of year: weeks starting on Monday (added in PHP 4.1.0)    Example: 42 (the 42nd week in the year)
        case 'F': //    A full textual representation of a month: such as January or March    January through December
        case 'u': //    Microseconds (added in PHP 5.2.2)    Example: 654321
        case 't': //    Number of days in the given month    28 through 31
        case 'L': //    Whether it's a leap year    1 if it is a leap year: 0 otherwise.
        case 'o': //    ISO-8601 year number. This has the same value as Y: except that if the ISO week number (W) belongs to the previous or next year: that year is used instead. (added in PHP 5.1.0)    Examples: 1999 or 2003
        case 'e': //    Timezone identifier (added in PHP 5.1.0)    Examples: UTC: GMT: Atlantic/Azores
        case 'I': // (capital i)    Whether or not the date is in daylight saving time    1 if Daylight Saving Time: 0 otherwise.
        case 'O': //    Difference to Greenwich time (GMT) in hours    Example: +0200
        case 'P': //    Difference to Greenwich time (GMT) with colon between hours and minutes (added in PHP 5.1.3)    Example: +02:00
        case 'T': //    Timezone abbreviation    Examples: EST: MDT ...
        case 'Z': //    Timezone offset in seconds. The offset for timezones west of UTC is always negative: and for those east of UTC is always positive.    -43200 through 50400
        case 'a': //    Lowercase Ante meridiem and Post meridiem    am or pm
        case 'A': //    Uppercase Ante meridiem and Post meridiem    AM or PM
        case 'B': //    Swatch Internet time    000 through 999
        case 'M': //    A short textual representation of a month: three letters    Jan through Dec
        default:
          $pos++;
      }
    }
return  $output;
  }
}
?>
