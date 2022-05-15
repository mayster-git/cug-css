const path = require('path');
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const main_cfg  = {
    entry: {
        'index.js': [
            './src/index.js',
            './src/editor.scss',
        ],        
    },
    output: {
        filename: '[name]',
        path: path.resolve( __dirname, 'build/' ),
    },
    mode: 'production',
    module: {
        rules: [
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@wordpress/babel-preset-default"]
                    }
                }
            },
            {
                test: /\.s[ac]ss$/i,
                exclude: /(node_modules|bower_components)/,
      
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name( resourcePath, resourceQuery ) {                    
                                rel = path.relative( path.resolve( __dirname, 'src/' ), resourcePath );
                                file = path.parse(rel);
                                return './' + file.dir + '/' + file.name + '.css';
                            },
                          },
                    }, 
                    {
                        loader: 'sass-loader'
                    }
                ]
            },
        ]
    },
    plugins: [ 
        new DependencyExtractionWebpackPlugin(),
    ],
    optimization: {
        minimize: true
    },
};

module.exports = [main_cfg]