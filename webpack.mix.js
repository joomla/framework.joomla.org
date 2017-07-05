let mix = require('laravel-mix');

// Configure base path for mix stuff going to web
mix.setPublicPath('www/');

// Configure base path for media assets
mix.setResourceRoot('/media');

// Core app JS
mix.js('assets/js/template.js', 'media/js');

// Core app CSS
mix
    .sass('assets/scss/template.scss', 'media/css')
    .options({
        // TODO - Eventually we'll want to postprocess URLs
        processCssUrls: false,
        postCss: [
            require('autoprefixer')({ browsers: 'last 2 versions' })
        ]
    });

// Copy Font Awesome icons
mix.copy('node_modules/font-awesome/fonts', 'www/media/fonts');
