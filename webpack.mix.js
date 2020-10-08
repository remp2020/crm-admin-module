let mix = require("laravel-mix");

mix
    .webpackConfig({
        watchOptions: {
            ignored: [ /node_modules([\\]+|\/)/ ]
        }
    })
    .options({
        publicPath: 'src/assets/dist',
        resourceRoot: "/layouts/admin/dist",
    })
    .js("src/assets/js/admin.js", "js/admin-module.min.js")
    .version();
