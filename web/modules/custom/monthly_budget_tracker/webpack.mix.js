let mix = require('laravel-mix');
mix.postCss("css/monthly-budget-tracker.css", "css/dist/app.css", [
    require("tailwindcss"),
]);