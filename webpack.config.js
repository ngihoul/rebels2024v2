const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    // .setPublicPath("/clubhouse/build")
    // PROD : To be updated
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    // .setManifestKeyPrefix("build/")

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('spinner', './assets/js/spinner.js')
    .addEntry('menu', './assets/js/menu.js')
    .addEntry('picturePreview', './assets/js/picturePreview.js')
    .addEntry('teamDropdown', './assets/js/teamDropdown.js')
    .addEntry('languageDropdown', './assets/js/languageDropdown.js')
    .addEntry('memberList', './assets/js/memberList.js')
    .addEntry('placeList', './assets/js/placeList.js')
    .addEntry('map', './assets/js/map.js')
    .addEntry('statistics', './assets/js/statistics.js')
    .addEntry('eventForm', './assets/js/eventForm.js')
    .addEntry('registerPassword', './assets/js/registerPassword.js')
    .addEntry('modalConfirmation', './assets/js/modalConfirmation.js')
    .addEntry(
        'autoCompletionAddPlayer',
        './assets/js/autoCompletionAddPlayer.js',
    )
    .addEntry('addChild', './assets/js/addChild.js')
    .addEntry('childrenRegistration', './assets/js/childrenRegistration.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv(config => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // CKEditor Config
    .copyFiles([
        {
            from: './node_modules/ckeditor4/',
            to: 'ckeditor/[path][name].[ext]',
            pattern: /\.(js|css)$/,
            includeSubdirectories: false,
        },
        {
            from: './node_modules/ckeditor4/adapters',
            to: 'ckeditor/adapters/[path][name].[ext]',
        },
        {
            from: './node_modules/ckeditor4/lang',
            to: 'ckeditor/lang/[path][name].[ext]',
        },
        {
            from: './node_modules/ckeditor4/plugins',
            to: 'ckeditor/plugins/[path][name].[ext]',
        },
        {
            from: './node_modules/ckeditor4/skins',
            to: 'ckeditor/skins/[path][name].[ext]',
        },
        {
            from: './node_modules/ckeditor4/vendor',
            to: 'ckeditor/vendor/[path][name].[ext]',
        },
    ])

    // enables Sass/SCSS support
    .enableSassLoader();

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()

module.exports = Encore.getWebpackConfig();
