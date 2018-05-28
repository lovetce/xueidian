<?php  
function timetostring($str){
    $cliptime=explode("-",$str);
    $result="";
    for($i=0;$i<count($cliptime);$i++){
        $result=$result.$cliptime[$i];
    }
    return $result;
}

    /**
     * 合成图片并输出到文件
     * $backgroud_path背景图片路径
     * $arr_picinfo需要合成的图片数组
     * $arrtextinfo需要在图片上生成的文字数组
     * $filepath输出图片文件,如果为空，直接输出流
     **/
    function createpic($backgroud_path,$arr_picinfo,$arrtextinfo,$filepath = NULL)
    {
        $pathInfo    = pathinfo($backgroud_path);
        switch( strtolower($pathInfo['extension']) ) {
            case 'jpg':
            case 'jpeg':
                $imagecreatefromjpeg    = 'imagecreatefromjpeg';
                break;
            case 'png':
                $imagecreatefromjpeg    = 'imagecreatefrompng';
                break;
            case 'gif':
            default:
                $imagecreatefromjpeg    = 'imagecreatefromstring';
                break;
        }
        $background   = $imagecreatefromjpeg($backgroud_path);
        
        
        
        $pic_list    = $arr_picinfo; 
        if (!empty($pic_list))
        foreach( $pic_list as $pic) {
            $pathInfo    = pathinfo($pic['path']);
            switch( strtolower($pathInfo['extension']) ) {
                case 'jpg':
                case 'jpeg':
                    $imagecreatefromjpeg    = 'imagecreatefromjpeg';
                    break;
                case 'png':
                    $imagecreatefromjpeg    = 'imagecreatefrompng';
                    break;
                case 'gif':
                default:
                    $imagecreatefromjpeg    = 'imagecreatefromstring';
                    break;
            }
            $resource   = $imagecreatefromjpeg($pic['path']);
            // $start_x,$start_y copy图片在背景中的位置
            // 0,0 被copy图片的位置
            // $pic_w,$pic_h copy后的高度和宽度
            imagecopyresized($background,$resource,$pic['posx'],$pic['posy'],0,0,$pic['width'],$pic['height'],imagesx($resource),imagesy($resource)); // 最后两个参数为原始图片宽度和高度，倒数两个参数为copy时的图片宽度和高度
            
        }
        
        if (!empty($arrtextinfo))
        {
            foreach( $arrtextinfo as $text) 
            {
                $be = imagecolorallocate($background,$text['rgb'][0], $text['rgb'][1], $text['rgb'][2]);//文字颜色
              
                
                //写字操作 $im为你载入的图片，第二个参数为 字体大小，第三个参数为旋转或倾斜度，第四为 离左边的距离，第五为，离上边的距离，第六为 字体颜色，第七为 字体，路径不能用网址，只能用相对，或绝对路径，第八为 要写入的 文字。
                imagettftext($background,$text['size'],$text['inclination'],$text['posx'],$text['posy'],$be,$text['font'],$text['text']);
            }
        }
        if(!empty($filepath))
        {
            imagejpeg($background, $filepath);
            imagedestroy($background);
            
        }
        else
        {
            
            //imagejpeg($background);
            return $background;
        }
    }
    
    
    function imagecropper($source_path, $target_width, $target_height,$filepath)
    {

        $source_info   = getimagesize($source_path);
        $source_width  = $source_info[0];
        $source_height = $source_info[1];
        $source_mime   = $source_info['mime'];
        $source_ratio  = $source_height / $source_width;
        $target_ratio  = $target_height / $target_width;
        
        // 源图过高
        if ($source_ratio > $target_ratio)
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_width * $target_ratio;
            $source_x = 0;
            $source_y = ($source_height - $cropped_height) / 2;
        }
        // 源图过宽
        elseif ($source_ratio < $target_ratio)
        {
            $cropped_width  = $source_height / $target_ratio;
            $cropped_height = $source_height;
            $source_x = ($source_width - $cropped_width) / 2;
            $source_y = 0;
        }
        // 源图适中
        else
        {
            $cropped_width  = $source_width;
            $cropped_height = $source_height;
            $source_x = 0;
            $source_y = 0;
        }
        
        switch ($source_mime)
        {
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;
        
            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;
        
            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;
        
            default:
                return false;
                break;
        }
        
        $target_image  = imagecreatetruecolor($target_width, $target_height);
        $cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
        
        // 裁剪
        imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
        // 缩放
        imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
        
        header('Content-Type: image/jpeg');
        imagejpeg($target_image,$filepath);
        
        imagedestroy($source_image);
        imagedestroy($target_image);
        imagedestroy($cropped_image);
    
    }