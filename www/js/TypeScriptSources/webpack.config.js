var path = require('path');

module.exports = {
    devtool: 'inline-source-map',
    entry: {
        all: './src/index.ts'
    },
    output: {
        path: path.resolve(__dirname, '../'),
        filename: 'bundle-[name].min.js',
        publicPath: '/js/',
    },
    module: {
        rules: [
            {
                enforce: 'pre',
                test: /\.tsx?$/,
                loader: 'tslint-loader',
                exclude: /node_modules/,
                options: {
                    failOnHint: true,
                    configuration: require('./tslint.json'),
                },
            },
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
        ],
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js'],
    },
    externals: {
        jquery: 'jQuery',
        React: 'react',
        ReactDOM: 'react-dom'
    },
};
