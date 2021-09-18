<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 22/02/2017
 * Time: 18:48
 */

namespace app\backend\modules\member\services;


use app\common\extensions\Validation;

class HandleNickname
{

    //处理微信昵称表情
    public function removeEmoji($clean_text) {

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $clean_text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        if( $clean_text && strpos($clean_text,'=') === 0 ){
            $clean_text = "'".$clean_text;
        }

        $clean_text = $this->removeEmojis($clean_text);

        return $clean_text;
    }

    //处理微信昵称表情
    public function removeEmojis($text){
        $len = mb_strlen($text);
        $newText = '';
        for($i=0;$i<$len;$i++){
            $str = mb_substr($text, $i, 1, 'utf-8');
            if(strlen($str) >= 4) continue;//emoji表情为4个字节
            $newText .= $str;
        }
        return $newText;
    }

}