const mix = require('laravel-mix');
require('laravel-mix-purgecss');

// Configure base path for mix stuff going to web
mix.setPublicPath('www/media/');

// Configure base path for media assets
mix.setResourceRoot('/media/');

// Core app JS
mix.js('assets/js/template.js', 'js');

// Core app CSS
mix
    .sourceMaps()
    .sass('assets/scss/template.scss', 'css')
    .options({
        postCss: [
            require('autoprefixer')()
        ]
    })
    .purgeCss({
        extend: {
            content: [
                'templates/**/*.twig',
            ],
        }
    })
;

// Version assets
mix.version();
