<?php
// This file is generated. Do not modify it manually.
return array(
	'brophy-quote-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'nn/brophy-quote-form',
		'version' => '0.1.0',
		'title' => 'Quote Form',
		'category' => 'brophy-quote-form',
		'icon' => 'feedback',
		'description' => 'Displays the quote form.',
		'attributes' => array(
			'heading' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'textdomain' => 'brophy-quote-form',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'quote-confirmation' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'nn/quote-confirmation',
		'version' => '0.1.0',
		'title' => 'Quote Confirmation',
		'category' => 'brophy-quote-form',
		'icon' => 'yes-alt',
		'description' => 'Message shown after a quote request.',
		'attributes' => array(
			'message' => array(
				'type' => 'string',
				'source' => 'html',
				'selector' => '.bqf-quote-confirmation__message',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'textdomain' => 'brophy-quote-form',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);
