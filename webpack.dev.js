const {merge} = require('webpack-merge');
const common = require('./webpack.common.js');
const ESLintPlugin = require("eslint-webpack-plugin");

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    plugins: [
        new ESLintPlugin({
            context: '../',
            failOnError: false,
            extensions: ['js', 'ts', 'tsx'],
            exclude: ['./lib', './vendor','./www'],
        }),
    ],
});
