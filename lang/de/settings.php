<?php
/**
 * German language file for imgpaste plugin
 *
 * @author Michael Fischer <dokuwiki@m-fischer.info>
 */

// keys need to match the config setting name
$lang['filename'] = 'So werden eingefügte Bilder benannt. Sie können die Platzhalter @USER@, @NS@, @ID@, @PAGE@ und die Platzhalter der PHP-Funktion strftime verwenden. Die Dateierweiterung wird automatisch hinzugefügt.';
$lang['format']   = 'Image format for pasted images. "original" keeps the clipboard format; "png" and "jpeg" re-encode the image on the server (PHP GD required). JPEG loses transparency and is composited on a white background.';
$lang['width']    = 'Default width in pixels appended to the inserted image syntax (e.g. ?640). Set to 0 to keep the original width.';
$lang['height']   = 'Default height in pixels appended together with the width (e.g. ?640x480). Set to 0 to keep the aspect ratio from the width only.';
$lang['jpegquality'] = 'JPEG quality for re-encoded images, 1 (lowest) to 100 (highest). Only applies when format is jpeg.';
$lang['randomlength'] = 'Length of the random lowercase alphanumeric string inserted by the @random@ filename placeholder.';


