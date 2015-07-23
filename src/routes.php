<?php

Route::group(['middleware' => 'auth', 'prefix' => 'adm'], function()
{
    Route::post('/uploadimage', 'Prehistorical\ImageFileLogic\ImageFileController@postImage');
});
