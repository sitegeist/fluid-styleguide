const path = require('path')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')

module.exports = {
  entry: './Resources/Private/Javascript/index.js',
  output: {
    filename: 'Main.min.js',
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
                  "useBuiltIns": "entry"
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
