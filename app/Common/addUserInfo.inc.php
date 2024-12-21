<?php
defined( 'LONGBING_NEW_AUTH' ) or define( 'LONGBING_NEW_AUTH', 1 );


$default_card      = 0;
$default_goods     = 0;
$default_timeline  = 0;
$default_message   = 0;
$default_custom_qr = 0;
$default_copyright = 1;
$default_mini      = 0;
$default_form      = 0;
$default_plug_auth = 0;
$default_article   = 0;
$default_activity  = 0;

//  版本
$version = 0;
//  无限开
$agent   = 1;

$version = intval( $version );

if ( !is_numeric( $version ) ) {
    $version = 0;
}

if ( $version == 0 ) {
    $default_card      = 5;
    $default_goods     = 5;
    $default_timeline  = 5;
    $default_message   = 5;
    $default_custom_qr = 5;
    $default_copyright = 1;
    $default_mini      = 2;
    $default_form      = 0;
    $default_plug_auth = 0;
} else {
    $default_mini = $version;
}

if ( $agent == 1 ) {
    $default_card      = 0;
    $default_goods     = 0;
    $default_timeline  = 0;
    $default_message   = 0;
    $default_custom_qr = 0;
    $default_copyright = 1;
    $default_mini      = 0;
    $default_form      = 0;
    $default_plug_auth = 0;
}