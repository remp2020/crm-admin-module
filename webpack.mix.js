let mix = require("laravel-mix");

mix
    // 2022 - temp fix of laravel-mix incompatibility with Apple Silicon
    // see https://github.com/laravel-mix/laravel-mix/issues/3027
    .disableNotifications()
    .options({
        publicPath: 'src/assets/dist',
        resourceRoot: "/layouts/admin/dist",
    })
    .js("src/assets/js/admin.js", "js/admin-module.min.js")
    .sass("src/assets/scss/vendor.scss", "css/admin-module.min.css")
    .version();
