var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/')
    .addEntry('contao-file-manager-bundle-be', './src/Resources/assets/js/contao-file-manager-bundle-be.js')
    .setPublicPath('/bundles/heimrichhannotfilemanager/')
    .setManifestKeyPrefix('bundles/heimrichhannotfilemanager')
    .enableSassLoader()
    .disableSingleRuntimeChunk()
    .addExternals({
        '@hundh/contao-utils-bundle': 'utilsBundle'
    })
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
