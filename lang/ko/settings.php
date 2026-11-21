<?php
/**
 * korean language file for imgpaste plugin
 *
 * @sc yoo  <dryoo@live.com>
 */

// keys need to match the config setting name
$lang['filename'] = '올린 파일의 이름. 대체 문자열 @USER@, @NS@, @ID@, @PAGE@, strftime을 사용할 수 있습니다. 파일확장자는 자동으로 더해집니다.';
$lang['format']   = 'Image format for pasted images. "original" keeps the clipboard format; "png" and "jpeg" re-encode the image on the server (PHP GD required). JPEG loses transparency and is composited on a white background.';
$lang['width']    = 'Default width in pixels appended to the inserted image syntax (e.g. ?640). Set to 0 to keep the original width.';
$lang['height']   = 'Default height in pixels appended together with the width (e.g. ?640x480). Set to 0 to keep the aspect ratio from the width only.';
$lang['jpegquality'] = 'JPEG quality for re-encoded images, 1 (lowest) to 100 (highest). Only applies when format is jpeg.';
$lang['randomlength'] = 'Length of the random lowercase alphanumeric string inserted by the @random@ filename placeholder.';



//Setup VIM: ex: et ts=4 :
