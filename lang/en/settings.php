<?php
/**
 * english language file for imgpaste plugin
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */

// keys need to match the config setting name
$lang['filename']    = 'How to name pasted images. You can use the placeholders @USER@, @NS@, @ID@, @PAGE@, @random@ and strftime placeholders. The file extension is added automatically.';
$lang['format']      = 'Image format for pasted images. "original" keeps the clipboard format; "png" and "jpeg" re-encode the image on the server (PHP GD required). JPEG loses transparency and is composited on a white background.';
$lang['width']       = 'Default width in pixels appended to the inserted image syntax (e.g. ?640). Set to 0 to keep the original width.';
$lang['height']      = 'Default height in pixels appended together with the width (e.g. ?640x480). Set to 0 to keep the aspect ratio from the width only.';
$lang['jpegquality'] = 'JPEG quality for re-encoded images, 1 (lowest) to 100 (highest). Only applies when format is jpeg.';
$lang['randomlength'] = 'Length of the random lowercase alphanumeric string inserted by the @random@ filename placeholder.';



//Setup VIM: ex: et ts=4 :
