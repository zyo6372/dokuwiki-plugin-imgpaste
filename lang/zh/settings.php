<?php
/**
 * Simplified Chinese language file for imgpaste plugin
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */

// keys need to match the config setting name
$lang['filename']     = '粘贴图片的命名方式。可使用占位符 @USER@、@NS@、@ID@、@PAGE@、@random@ 以及 strftime 时间占位符，文件扩展名会自动添加。';
$lang['format']       = '粘贴图片的保存格式。"original" 保持剪贴板原格式；"png" 和 "jpeg" 在服务端重编码图片（需要 PHP GD 扩展）。JPEG 不支持透明，透明区域会被合成到白色背景上。';
$lang['width']        = '追加到图片语法后的默认宽度（像素，例如 ?640）。设为 0 表示保持原始宽度。';
$lang['height']       = '与宽度同时追加的默认高度（像素，例如 ?640x480）。设为 0 表示仅按宽度等比缩放。';
$lang['jpegquality']  = '重编码图片的 JPEG 质量，1（最低）到 100（最高）。仅在格式为 jpeg 时生效。';
$lang['randomlength'] = '由 @random@ 文件命名占位符生成的随机小写字母与数字串的长度。';



//Setup VIM: ex: et ts=4 :
