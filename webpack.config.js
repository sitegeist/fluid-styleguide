const path = require('path')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')

module.exports = {
    entry: {
        'Styleguide': './Resources/Private/Javascript/Styleguide.js',
        'Iframe': './Resources/Private/Javascript/Iframe.js'
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve('Resources', 'Public', 'Javascript')
    },
    // stats: 'minimal',
    mode: 'development',
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        "presets": [
                            [
                                "@babel/preset-env",
                                {
                                    "modules": false,
                                    "corejs": "3.6.5",
                                    "useBuiltIns": "usage"
                                }
                            ]
                        ],
                        "plugins": [
                            "@babel/plugin-transform-runtime",
                            "@babel/plugin-proposal-class-properties"
                        ]
                    }
                }
            }
        ]
    },
    optimization: {
        minimizer: [
            new UglifyJsPlugin({
                cache: true,
                parallel: true,
                sourceMap: true
            })
        ]
    },
    watchOptions: {
        ignored: /node_modules/
    }
}
