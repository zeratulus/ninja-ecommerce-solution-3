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
            $new_filename = HTTPS_CATALOG . 'image/' . $new_image;
        } else {
            $new_filename = HTTP_CATALOG . 'image/' . $new_image;
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
            $new_filename = str_replace('.webp', '@2x.png', $new_filename);
        }

        return $new_filename;
    }

}
