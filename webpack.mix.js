let mix = require('laravel-mix');

// Configure base path for mix stuff going to web
mix.setPublicPath('www/media/');

// Configure base path for media assets
mix.setResourceRoot('/media/');

// Core app JS
mix.js('assets/js/template.js', 'js');

// Core app CSS
mix
    .sass('assets/scss/template.scss', 'css')
    .options({
        postCss: [
            require('autoprefixer')({ browsers: 'last 2 versions' })
        ]
    });

// Version assets
mix.version();
