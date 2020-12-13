var path = require('path');
const {TsConfigPathsPlugin} = require('awesome-typescript-loader');

module.exports = {
    entry: './app/Bootstrap.tsx',
    output: {
        path: __dirname + '/www/js/',
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
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    "style-loader",
                    // Translates CSS into CommonJS
                    "css-loader",
                    // Compiles Sass to CSS
                    {
                        loader: 'sass-loader',
                        options: {
                            webpackImporter: false,
                            sassOptions: {
                                includePaths: ['node_modules'],
                            },
                            implementation: require('sass'),
                        },
                    },
                ],
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
