var path = require('path');

module.exports = {
    devtool: 'inline-source-map',
    entry: {
        //  results: './src/results/index.tsx',
        // routing: './src/routing/index.tsx',
        // 'entry-form': './src/entry-form/index.tsx',
        'brawl-registration': './src/brawl-registration/components/index.tsx',
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
                    failOnHint: false,
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
