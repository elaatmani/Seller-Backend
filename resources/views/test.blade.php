<?php

use \ArPHP\I18N\Arabic;


function translateProductNameToArabic($productName)
{
    try {
        $arabic = app(Arabic::class);
        return $arabic->utf8Glyphs($productName) ?? $productName;
    } catch (Exception $e) {
        return $productName;
    }
    
}


echo translateProductNameToArabic('AFL27 بخاخ الزنجبيل الاخضر لمعالجة تساقط الشعر');