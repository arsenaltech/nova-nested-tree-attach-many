<?php

Route::get('/{resource}/{resourceId}/attached/{relationship}/{idKey}', 'PhoenixLib\NovaNestedTreeAttachMany\Http\Controllers\NestedTreeController@attached');
Route::get('/get-category-full-path', 'PhoenixLib\NovaNestedTreeAttachMany\Http\Controllers\NestedTreeController@getCategoryFullPath');
