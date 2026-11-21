<?php
/**
 * Options for the imgpaste plugin
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */


$meta['filename'] = array('string');
$meta['format']   = array(
    'multichoice',
    '_choices' => array('original', 'png', 'jpeg'),
);
$meta['width']        = array('numeric', '_min' => 0);
$meta['height']       = array('numeric', '_min' => 0);
$meta['jpegquality']  = array('numeric', '_min' => 1, '_max' => 100);
$meta['randomlength']  = array('numeric', '_min' => 1, '_max' => 32);
