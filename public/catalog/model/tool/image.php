<?php
class ModelToolImage extends Model {

    private function getBrowserDetection(): \BrowserDetection {
        return $this->registry->get('browserdetection');
    }

    public function resize($filename, $width, $height, $isRetina = true, $quality = 90)
    {
        if (!is_file(DIR_IMAGE . $filename)) {
            return;
        }

        $old_image = $filename;

        $image_filename = replaceSpaces(removeSymbols($filename)) . '-' . $width . 'x' . $height . '_' . $this->config->get('config_store_id');
        if (!$isRetina) {
            $new_image = 'cache/' . $image_filename . '.webp';
        } else {
            $new_image = 'cache/' . $image_filename . '.webp';
            //@2x images - PNG because safari do not support WEBP images
            $new_image2x = 'cache/' . $image_filename . '@2x.png';
        }

        if ($this->request->server['HTTPS']) {
            $image_url = HTTPS_SERVER . 'image/' . $new_image;
        } else {
            $image_url = HTTP_SERVER . 'image/' . $new_image;
        }

        $cache_file = DIR_IMAGE . $new_image;

        if (!is_file($cache_file) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

            if ($width_orig != $width || $height_orig != $height) {
                //@2x - png - no safari support of webp
                if ($isRetina) {
                    $cache_file2x = DIR_IMAGE . $new_image2x;
                    $image2x = new Image(DIR_IMAGE . $old_image);
                    $image2x->resize(round($width * 2), round($height * 2), $quality);
                    $image2x->save($cache_file2x);
                }

                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($width, $height, $quality);
                $image->save($cache_file);
            } else {
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }

        }

        //return png if safari
        if ($this->getBrowserDetection()->getName() == \BrowserDetection::BROWSER_SAFARI) {
            $image_url = str_replace('.webp', '@2x.png', $image_url);
        }

        return $image_url;
    }

    public function resizeSiteMap($filename, $width, $height) {
        $image_filename = replaceSpaces(removeSymbols($filename)) . '-' . $width . 'x' . $height . '_' . $this->config->get('config_store_id');
        $cache = 'cache/' . $image_filename . '@2x.png';
        if ($this->request->server['HTTPS']) {
            $new_filename = HTTPS_SERVER . 'image/' . $cache;
        } else {
            $new_filename = HTTP_SERVER . 'image/' . $cache;
        }

        return $new_filename;
    }

    //proportional scale by width or height
    public function resizeBanner($filename, $width, $height, $isRetina = true, $quality = 90)
    {
        if (!is_file(DIR_IMAGE . $filename)) {
            return;
        }

        $old_image = $filename;
        $image_filename = replaceSpaces(removeSymbols($filename)) . '-' . $width . 'x' . $height . '_' . $this->config->get('config_store_id');
        if (!$isRetina) {
            $new_image = 'cache/' . $image_filename . '.webp';
        } else {
            $new_image = 'cache/' . $image_filename . '.webp';
            //@2x images - jpg becauase safari do not support Webp images
            $new_image2x = 'cache/' . $image_filename . '@2x.jpg';
        }

        if ($this->request->server['HTTPS']) {
            $new_filename = HTTPS_SERVER . 'image/' . $new_image;
        } else {
            $new_filename = HTTP_SERVER . 'image/' . $new_image;
        }

        $cache_file = DIR_IMAGE . $new_image;

        if (!is_file($cache_file) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            //we can resize and convert this
            $allowed = getImageExtensions();

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($extension, $allowed)) {
                switch ($extension) {
                    case 'png':
                        $image = imagecreatefrompng(DIR_IMAGE . $filename);
                        break;
                    case 'jpg':
                        $image = imagecreatefromjpeg(DIR_IMAGE . $filename);
                        break;
                    case 'jpeg':
                        $image = imagecreatefromjpeg(DIR_IMAGE . $filename);
                        break;
                    case 'bmp':
                        $image = imagecreatefrombmp(DIR_IMAGE . $filename);
                        break;
                    case 'gif':
                        $image = imagecreatefromgif(DIR_IMAGE . $filename);
                        break;
                    case 'webp':
                        $image = imagecreatefromwebp(DIR_IMAGE . $filename);
                        break;
                }

                if (!is_file($cache_file)) {
                    $scaled_image = imagescale($image, $width, -1, IMG_BICUBIC);
                    imagewebp($scaled_image, $cache_file, $quality);
                    imagedestroy($scaled_image);
                }

                //@2x - jpg - no safari support of webp
                if ($isRetina) {
                    $cache_file2x = DIR_IMAGE . $new_image2x;
                    if (!is_file($cache_file2x)) {
                        $scaled_image = imagescale($image, round($width * 2), -1, IMG_BICUBIC);
                        imagejpeg($scaled_image, $cache_file2x, $quality);
                        imagedestroy($scaled_image);
                    }
                }

                imagedestroy($image);
            }
        }

        //return jpeg if safari
        if ($this->getBrowserDetection()->getName() == \BrowserDetection::BROWSER_SAFARI) {
            $new_filename = str_replace('.webp', '@2x.jpg', $new_filename);
        }

        return $new_filename;
    }

//  Default OpenCart algorithm
//
//	public function resize($filename, $width, $height) {
//		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
//			return;
//		}
//
//		$extension = pathinfo($filename, PATHINFO_EXTENSION);
//
//		$image_old = $filename;
//		$image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;
//
//		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
//			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);
//
//			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
//				return DIR_IMAGE . $image_old;
//			}
//
//			$path = '';
//
//			$directories = explode('/', dirname($image_new));
//
//			foreach ($directories as $directory) {
//				$path = $path . '/' . $directory;
//
//				if (!is_dir(DIR_IMAGE . $path)) {
//					@mkdir(DIR_IMAGE . $path, 0777);
//				}
//			}
//
//			if ($width_orig != $width || $height_orig != $height) {
//				$image = new Image(DIR_IMAGE . $image_old);
//				$image->resize($width, $height);
//				$image->save(DIR_IMAGE . $image_new);
//			} else {
//				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
//			}
//		}
//
//		$image_new = str_replace(' ', '%20', $image_new);  // fix bug when attach image on email (gmail.com). it is automatic changing space " " to +
//
//		if ($this->request->server['HTTPS']) {
//			return $this->config->get('config_ssl') . 'image/' . $image_new;
//		} else {
//			return $this->config->get('config_url') . 'image/' . $image_new;
//		}
//	}

}
