let mix = require('laravel-mix');
mix.postCss("css/monthly-budget-tracker.css", "css/dist/app.css", [
    require("tailwindcss")]);
// ]).browserSync({
//     proxy: 'https://personal-budget.ddev.site',
//     files: [
//         './templates/**/*.html.twig',
//         'js/*.js',
//         'css/*.css',
//     ],
// });