let mix = require('laravel-mix')
const path = require('path')
require('./nova.mix')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .sass('resources/sass/field.scss', 'css')
    .nova('phoenix-lib/nova-nested-tree-attach-many');
