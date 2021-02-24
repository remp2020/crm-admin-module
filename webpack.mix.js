let mix = require("laravel-mix");

mix
    .options({
        publicPath: 'src/assets/dist',
        resourceRoot: "/layouts/admin/dist",
    })
    .js("src/assets/js/admin.js", "js/admin-module.min.js")
    .sass("src/assets/scss/vendor.scss", "css/admin-module.min.css")
    .version();
