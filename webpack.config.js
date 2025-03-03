const path = require('path');
const webpack = require('webpack');

module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';

    return {
        // Entry point of the application
        entry: './frontend/index.js',

        // Output configuration
        output: {
            path: path.resolve(__dirname, 'dist'),
            filename: 'bundle.[contenthash].js',
            clean: true  // Clean the output directory before emit
        },

        // Module resolution and loaders
        module: {
            rules: [
                {
                    test: /\.(js|jsx)$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                '@babel/preset-env', 
                                '@babel/preset-react'
                            ]
                        }
                    }
                },
                {
                    test: /\.css$/,
                    use: ['style-loader', 'css-loader']
                },
                {
                    test: /\.(png|svg|jpg|jpeg|gif)$/i,
                    type: 'asset/resource'
                }
            ]
        },

        // Resolve extensions and aliases
        resolve: {
            extensions: ['.js', '.jsx'],
            alias: {
                '@': path.resolve(__dirname, 'frontend')
            }
        },

        // Plugins
        plugins: [
            // Define environment variables
            new webpack.DefinePlugin({
                'process.env.NODE_ENV': JSON.stringify(argv.mode),
                'process.env.APP_VERSION': JSON.stringify(require('./package.json').version)
            })
        ],

        // Development server configuration
        devServer: {
            static: {
                directory: path.join(__dirname, 'frontend')
            },
            compress: true,
            port: 8080,
            open: true,
            hot: true,
            historyApiFallback: true
        },

        // Source map configuration
        devtool: isProduction ? 'source-map' : 'eval-source-map',

        // Performance hints
        performance: {
            hints: isProduction ? 'warning' : false,
            maxEntrypointSize: 250000,
            maxAssetSize: 250000
        },

        // Optimization for production
        optimization: {
            minimize: isProduction,
            splitChunks: {
                chunks: 'all',
                name: false,
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all'
                    }
                }
            }
        }
    };
};