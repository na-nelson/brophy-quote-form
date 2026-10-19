const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		form: './src/form/view.js',
		admin: './src/admin/index.js',
	},
};
