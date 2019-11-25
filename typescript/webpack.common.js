var path = require('path');
const {TsConfigPathsPlugin} = require('awesome-typescript-loader');

module.exports = {
    entry: './src/index.ts',
    output: {
        path: __dirname + '/../www/js/',
        filename: 'bundle.min.js',
        publicPath: 'js',
    },
    module: {
        rules: [
            {
                enforce: 'pre',
                test: /\.tsx?$/,
                loader: 'tslint-loader',
                exclude: /node_modules/,
                options: {
                    failOnHint: false,
                    configuration: require('./tslint.json'),
                },
            },
            {
                test: /\.tsx?$/,
                use: 'awesome-typescript-loader',
                //use: 'ts-loader',
                exclude: /node_modules/,
            },
        ],
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js'],
        plugins: [
            new TsConfigPathsPlugin(/* { configFileName, compiler } */)
        ]
    },
    externals: {
        jquery: 'jQuery',
        React: 'react',
        ReactDOM: 'react-dom'
    },
};
