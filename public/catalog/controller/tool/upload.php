<?php
class ControllerToolUpload extends Controller {
	public function index() {
		$this->load->language('tool/upload');

		$json = array();

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    //Get base64 encoded image - from&for Croppie JS - for avatars in reviews
    public function avatar() {
        $json = array();

        if (!empty($image = $this->request->isset_post('image')) &&
            !empty($name = $this->request->isset_post('name')) &&
            !empty($type = $this->request->isset_post('type')) && ($type === 'product' || $type === 'article')
        ) {
            //data:image/png;
            $mime_part = 'data:image/';

            //base64,gAAAQ8AAAC6CAMAAACHgTh+AA=";
            $img_part = ';base64,';

            //get ext
            $pos_start_ext = strpos($image, $mime_part) + strlen($mime_part);
            $pos_end_ext = strpos($image, ';', $pos_start_ext);
            $extension = substr($image, $pos_start_ext, $pos_end_ext - $pos_start_ext);

            //get image$type
            $pos_start_img = strpos($image, $img_part) + strlen($img_part);
            $pos_end_img = strlen($image);
            $encoded_img = substr($image, $pos_start_img, $pos_end_img - $pos_start_img);

            $image_data = base64_decode($encoded_img);

            if ($image_data === false) $json['error'] = 'Base64 decode failed';
        } else {
            $json['error'] = 'Request image not found ';
        }


        if (!$json) {
            $date = replaceSpaces(str_replace(':','-', nowMySQLTimestamp()));
            $name = utf8_strtolower(transliterate(replaceSpaces($name)));
            $new_filename = "{$date}-{$name}.{$extension}";
            $dir = DIR_IMAGE . "avatars/{$type}/";
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $result = file_put_contents($dir . $new_filename, $image_data);

            $json['success'] = $result;
            $json['file'] = $new_filename;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}