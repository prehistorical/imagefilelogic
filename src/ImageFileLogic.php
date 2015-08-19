<?php

namespace Prehistorical\ImageFileLogic;

use URL;
use Intervention\Image\Facades\Image;

class ImageFileLogic {

    public static function getMkFileMode(){
        return 0777;
    }

    public static function getSizes($variant){

        $config = config('resize.'.$variant);
        if (!$config){
            $config = ['sizes'=>
                [
                    ['width'=>75, 'height'=>75, 'sufix'=>'preview']
                ],
                'preview_sufix' => 'preview'
            ];
        }

        return $config;
    }

    public static function createResized($filename, $width, $height, $sufix) {

        if ($filename) {

            $mkfile_mode = static::getMkFileMode();

            $filepath = public_path() . '/images/' . $filename;

            $inf = pathinfo($filepath);
            //$newfilepath = public_path() . '/images/resized/' . $inf['filename'] . '_' .$sufix . '.' . $inf['extension'];
            $newfilepath = public_path() . '/images/' . $inf['filename'] . '_' .$sufix . '.' . $inf['extension'];

            if (file_exists($filepath)) {

                $img = Image::make($filepath)->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $img->save($newfilepath, 100);

                chmod($newfilepath, $mkfile_mode);

                return $newfilepath;
            }
        }
    }

    static function getResizedName($config, $inf){

        if(array_key_exists('primary_sufix', $config)){
            $resizedfilename = $inf['filename'].'_'.$config['primary_sufix'].'.'.$inf['extension'];
        } else {
            $resizedfilename = $inf['filename'].'.'.$inf['extension'];
        }

        return $resizedfilename;
    }

    static function getAdditionalName($config, $inf){

        if(array_key_exists('secondary_sufix', $config)){
            $resizedfilename = $inf['filename'].'_'.$config['secondary_sufix'].'.'.$inf['extension'];
        } else {
            $resizedfilename = $inf['filename'].'.'.$inf['extension'];
        }

        return $resizedfilename;
    }

    static function getPreviewName($config, $inf){

        if(array_key_exists('preview_sufix', $config)){
            $resizedfilename = $inf['filename'].'_'.$config['preview_sufix'].'.'.$inf['extension'];
        } else {
            $resizedfilename = $inf['filename'].'.'.$inf['extension'];
        }

        return $resizedfilename;
    }

    static function getSecondaryName($config, $inf){

        if(array_key_exists('secondary_sufix', $config)){
            $resizedfilename = $inf['filename'].'_'.$config['secondary_sufix'].'.'.$inf['extension'];
        } else {
            $resizedfilename = $inf['filename'].'.'.$inf['extension'];
        }

        return $resizedfilename;
    }

    public static function removeForPrefix($prefix) {

        $dir = public_path() . '/images';

        foreach (glob($dir.'/'.$prefix.'*.*') as $file) {

            if(is_dir($dir.'/'.$file) || $file=='.' || $file=='..') continue;

            unlink($dir.'/'.$file);
        }

//        //Поиск в папке resized удалить
//        foreach (glob($dir.'/resized/'.$prefix.'*.*') as $file) {
//
//            if(is_dir($dir.'/resized/'.$file)) continue;
//
//            unlink($dir.'/resized/'.$file);
//        }
    }

    public function storeImage($entity, $id, $image, $include_filename=true) {

        $baseurl = URL::to('/');

        $prefix = $entity.'_'.$id;

        $mkfile_mode = static::getMkFileMode();

        $resp_arr = array();

        $dir = public_path() . '/images';

        $imagename = $image->getClientOriginalName();
        $imagesize = $image->getSize();

        if($include_filename){
            $newimagename = $prefix.'_'.$imagename;
        }else{
            $extension = $image->getClientOriginalExtension();
            $newimagename = $prefix.'.'.$extension;
        }

        $image_path = $dir.'/'.$newimagename;

        $uploadflag = $image->move($dir, $newimagename);

        if ($uploadflag) {

            $chmodyes = false;

            $chmodyes = chmod($image_path, $mkfile_mode);

            $inf = pathinfo($image_path);

            //$baseurl_plus = $baseurl.'/images/';

            //Создание файлов в папке resized
            $config = static::getSizes($entity);

            $sizes = $config['sizes'];

            foreach($sizes as $size) {
                static::createResized($newimagename, $size['width'], $size['height'], $size['sufix']);
            }

            $resizedfilename = static::getResizedName($config, $inf);
            $previewfilename = static::getPreviewName($config, $inf);
            $secfilename = static::getSecondaryName($config, $inf);

            $resp_arr['status'] = 'OK';
            $resp_arr['preview_file_name'] = $previewfilename;
            $resp_arr['file_name'] = $resizedfilename;
            $resp_arr['file_name_sec'] = $secfilename;

        } else {

            $resp_arr['status'] = 'Ошибка в правах доступа к дирректории картинок при записи!';

        }

        return $resp_arr;
    }



} 