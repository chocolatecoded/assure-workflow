let mix = require('laravel-mix');

mix.setPublicPath('public');

mix.js('resources/assets/js/workflow.js', 'public/js')
   .sass('resources/assets/sass/workflow.scss', 'public/css')
   .options({ processCssUrls: true })
   .copy('node_modules/font-awesome/fonts', 'public/fonts');

