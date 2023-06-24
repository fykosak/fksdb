var path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TsconfigPathsPlugin = require('tsconfig-paths-webpack-plugin');

module.exports = {
    entry: './app/Bootstrap.tsx',
    output: {
        path: path.resolve(__dirname, './www/assets'),
        assetModuleFilename: 'media/[path][name][ext]',
        filename: 'bundle.min.js',
    },
    plugins: [
        new MiniCssExtractPlugin(),
    ],
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                //use: 'ts-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.(css)$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader']
            },
            {
                test: /\.s[ac]ss$/i,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'resolve-url-loader', {
                    loader: 'sass-loader',
                    options: {
                        sourceMap: true
                    }
                }],
            },
        ],
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js', '.scss'],
        plugins: [new TsconfigPathsPlugin()],
    },
    externals: {
        jquery: 'jQuery',
    },
};
