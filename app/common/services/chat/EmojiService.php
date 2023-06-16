<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/5/25
 * Time: 15:37
 */

namespace app\common\services\chat;

use app\common\helpers\Url;

class EmojiService
{
    public static function emojiList()
    {
        return [
            [
                'name' => 'face[01]',
                'text' => '[微笑]',
                'url' => 'emoji/img/01.png',
                'code' => '/::)'
            ],
            [
                'name' => 'face[02]',
                'text' => '[撇嘴]',
                'url' => 'emoji/img/02.png',
                'code' => '/::~'
            ],
            [
                'name' => 'face[03]',
                'text' => '[色]',
                'url' => 'emoji/img/03.png',
                'code' => '/::B'
            ],
            [
                'name' => 'face[04]',
                'text' => '[发呆]',
                'url' => 'emoji/img/04.png',
                'code' => '/::|'
            ],
            [
                'name' => 'face[05]',
                'text' => '[得意]',
                'url' => 'emoji/img/05.png',
                'code' => '/:8-)'
            ],
            [
                'name' => 'face[06]',
                'text' => '[流泪]',
                'url' => 'emoji/img/06.png',
                'code' => '/::<'
            ],
            [
                'name' => 'face[07]',
                'text' => '[害羞]',
                'url' => 'emoji/img/07.png',
                'code' => '/::$'
            ],
            [
                'name' => 'face[08]',
                'text' => '[闭嘴]',
                'url' => 'emoji/img/08.png',
                'code' => '/::X'
            ],
            [
                'name' => 'face[09]',
                'text' => '[睡]',
                'url' => 'emoji/img/09.png',
                'code' => '/::Z'
            ],
            [
                'name' => 'face[09]',
                'text' => '[大哭]',
                'url' => 'emoji/img/09.png',
                'code' => "/::'("
            ],
            [
                'name' => 'face[11]',
                'text' => '[尴尬]',
                'url' => 'emoji/img/11.png',
                'code' => '/::-|'
            ],
            [
                'name' => 'face[12]',
                'text' => '[发怒]',
                'url' => 'emoji/img/12.png',
                'code' => '/::@'
            ],
            [
                'name' => 'face[13]',
                'text' => '[调皮]',
                'url' => 'emoji/img/13.png',
                'code' => '/::P'
            ],
            [
                'name' => 'face[14]',
                'text' => '[呲牙]',
                'url' => 'emoji/img/14.png',
                'code' => '/::D'
            ],
            [
                'name' => 'face[15]',
                'text' => '[惊讶]',
                'url' => 'emoji/img/15.png',
                'code' => '/::O'
            ],
            [
                'name' => 'face[16]',
                'text' => '[难过]',
                'url' => 'emoji/img/16.png',
                'code' => '/::('
            ],
            [
                'name' => 'face[17]',
                'text' => '[囧]',
                'url' => 'emoji/img/17.png',
                'code' => '/:--b'
            ],
            [
                'name' => 'face[18]',
                'text' => '[抓狂]',
                'url' => 'emoji/img/18.png',
                'code' => '/::Q'
            ],
            [
                'name' => 'face[19]',
                'text' => '[吐]',
                'url' => 'emoji/img/19.png',
                'code' => '/::T'
            ],
            [
                'name' => 'face[20]',
                'text' => '[偷笑]',
                'url' => 'emoji/img/20.png',
                'code' => '/:,@P'
            ],
            [
                'name' => 'face[21]',
                'text' => '[愉快]',
                'url' => 'emoji/img/21.png',
                'code' => '/:,@-D'
            ],
            [
                'name' => 'face[22]',
                'text' => '[白眼]',
                'url' => 'emoji/img/22.png',
                'code' => '/::d'
            ],
            [
                'name' => 'face[23]',
                'text' => '[傲慢]',
                'url' => 'emoji/img/23.png',
                'code' => '/:,@o'
            ],
            [
                'name' => 'face[24]',
                'text' => '[困]',
                'url' => 'emoji/img/24.png',
                'code' => '/:|-)'
            ],
            [
                'name' => 'face[25]',
                'text' => '[惊恐]',
                'url' => 'emoji/img/25.png',
                'code' => '/::g'
            ],
            [
                'name' => 'face[26]',
                'text' => '[流汗]',
                'url' => 'emoji/img/26.png',
                'code' => '/::L'
            ],
            [
                'name' => 'face[27]',
                'text' => '[憨笑]',
                'url' => 'emoji/img/27.png',
                'code' => '/::>'
            ],
            [
                'name' => 'face[28]',
                'text' => '[悠闲]',
                'url' => 'emoji/img/28.png',
                'code' => '/::,@'
            ],
            [
                'name' => 'face[29]',
                'text' => '[奋斗]',
                'url' => 'emoji/img/29.png',
                'code' => '/:,@f'
            ],
            [
                'name' => 'face[30]',
                'text' => '[咒骂]',
                'url' => 'emoji/img/30.png',
                'code' => '/::-S'
            ],
            [
                'name' => 'face[31]',
                'text' => '[疑问]',
                'url' => 'emoji/img/31.png',
                'code' => '/:?'
            ],
            [
                'name' => 'face[32]',
                'text' => '[嘘]',
                'url' => 'emoji/img/32.png',
                'code' => '/:,@x'
            ],
            [
                'name' => 'face[33]',
                'text' => '[晕]',
                'url' => 'emoji/img/33.png',
                'code' => '/:,@@'
            ],
            [
                'name' => 'face[34]',
                'text' => '[衰]',
                'url' => 'emoji/img/34.png',
                'code' => '/:,@!'
            ],
            [
                'name' => 'face[35]',
                'text' => '[敲打]',
                'url' => 'emoji/img/35.png',
                'code' => '/:xx'
            ],
            [
                'name' => 'face[36]',
                'text' => '[再见]',
                'url' => 'emoji/img/36.png',
                'code' => '/:bye'
            ],
            [
                'name' => 'face[37]',
                'text' => '[擦汗]',
                'url' => 'emoji/img/37.png',
                'code' => '/:wipe'
            ],
            [
                'name' => 'face[38]',
                'text' => '[抠鼻]',
                'url' => 'emoji/img/38.png',
                'code' => '/:dig'
            ],
            [
                'name' => 'face[39]',
                'text' => '[鼓掌]',
                'url' => 'emoji/img/39.png',
                'code' => '/:handclap'
            ],
            [
                'name' => 'face[40]',
                'text' => '[坏笑]',
                'url' => 'emoji/img/40.png',
                'code' => '/:B-)'
            ],
            [
                'name' => 'face[41]',
                'text' => '[左哼哼]',
                'url' => 'emoji/img/41.png',
                'code' => '/:<@'
            ],
            [
                'name' => 'face[42]',
                'text' => '[右哼哼]',
                'url' => 'emoji/img/42.png',
                'code' => '/:@>'
            ],
            [
                'name' => 'face[43]',
                'text' => '[哈欠]',
                'url' => 'emoji/img/43.png',
                'code' => '/::-O'
            ],
            [
                'name' => 'face[44]',
                'text' => '[鄙视]',
                'url' => 'emoji/img/44.png',
                'code' => '/:>-|'
            ],
            [
                'name' => 'face[45]',
                'text' => '[委屈]',
                'url' => 'emoji/img/45.png',
                'code' => '/:P-('
            ],
            [
                'name' => 'face[46]',
                'text' => '[快哭了]',
                'url' => 'emoji/img/46.png',
                'code' => "/::'|"
            ],
            [
                'name' => 'face[47]',
                'text' => '[阴险]',
                'url' => 'emoji/img/47.png',
                'code' => '/:X-)'
            ],
            [
                'name' => 'face[48]',
                'text' => '[亲亲]',
                'url' => 'emoji/img/48.png',
                'code' => '/::*'
            ],
            [
                'name' => 'face[49]',
                'text' => '[可怜]',
                'url' => 'emoji/img/49.png',
                'code' => '/:8*'
            ],
            [
                'name' => 'face[57]',
                'text' => '[爱心]',
                'url' => 'emoji/img/57.png',
                'code' => '/:heart'
            ],
            [
                'name' => 'face[58]',
                'text' => '[心碎]',
                'url' => 'emoji/img/58.png',
                'code' => '/:break'
            ],
            [
                'name' => 'face[59]',
                'text' => '[蛋糕]',
                'url' => 'emoji/img/59.png',
                'code' => '/:cake'
            ],
            [
                'name' => 'face[60]',
                'text' => '[月亮]',
                'url' => 'emoji/img/60.png',
                'code' => '/:moon'
            ],
            [
                'name' => 'face[61]',
                'text' => '[太阳]',
                'url' => 'emoji/img/61.png',
                'code' => '/:sun'
            ],
            [
                'name' => 'face[62]',
                'text' => '[拥抱]',
                'url' => 'emoji/img/62.png',
                'code' => '/:hug'
            ],
            [
                'name' => 'face[63]',
                'text' => '[强]',
                'url' => 'emoji/img/63.png',
                'code' => '/:strong'
            ],
            [
                'name' => 'face[64]',
                'text' => '[弱]',
                'url' => 'emoji/img/64.png',
                'code' => '/:MMWeak'
            ],
            [
                'name' => 'face[65]',
                'text' => '[握手]',
                'url' => 'emoji/img/65.png',
                'code' => '/:share'
            ],
            [
                'name' => 'face[66]',
                'text' => '[胜利]',
                'url' => 'emoji/img/66.png',
                'code' => '/:v'
            ],
            [
                'name' => 'face[67]',
                'text' => '[抱拳]',
                'url' => 'emoji/img/67.png',
                'code' => '/:@)'
            ],
            [
                'name' => 'face[68]',
                'text' => '[勾引]',
                'url' => 'emoji/img/68.png',
                'code' => '/:jj'
            ],
            [
                'name' => 'face[69]',
                'text' => '[拳头]',
                'url' => 'emoji/img/69.png',
                'code' => '/:@@'
            ],
            [
                'name' => 'face[70]',
                'text' => '[OK]',
                'url' => 'emoji/img/70.png',
                'code' => '/:ok'
            ],
            [
                'name' => 'face[83]',
                'text' => '[嘿哈]',
                'url' => 'emoji/img/83.png',
                'code' => '[Hey]'
            ],
            [
                'name' => 'face[84]',
                'text' => '[捂脸]',
                'url' => 'emoji/img/84.png',
                'code' => '[Facepalm]'
            ],
            [
                'name' => 'face[85]',
                'text' => '[奸笑]',
                'url' => 'emoji/img/85.png',
                'code' => '[Smirk]'
            ],
            [
                'name' => 'face[86]',
                'text' => '[机智]',
                'url' => 'emoji/img/86.png',
                'code' => '[Smart]'
            ],
            [
                'name' => 'face[87]',
                'text' => '[皱眉]',
                'url' => 'emoji/img/87.png',
                'code' => '[Concerned]'
            ],
            [
                'name' => 'face[88]',
                'text' => '[耶]',
                'url' => 'emoji/img/88.png',
                'code' => '[Yeah!]'
            ]
        ];
    }

    /**
     * 图片返回绝对地址，给前端使用
     * @return array
     */
    public static function getAllWebEmojis(){
        $res = self::emojiList();
        foreach ($res as $k=>$v){

            $res[$k]['url'] = Url::shopSchemeUrl(static_url($v['url']));
        }
        return $res;
    }
}