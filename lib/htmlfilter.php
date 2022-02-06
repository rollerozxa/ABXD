<?php

require_once 'bbcode.php';

$filter_args = [
	'class'           => TRUE,
	// For abusers, like Nina
	'contenteditable' => TRUE,
	'dir'             => 'process_dir',
	'hidden'          => 'process_null_attr',
	'id'              => 'process_id',
	'lang'            => TRUE,
	// This is only useful with contenteditable, but whatever
	'spellcheck'      => 'process_truefalse',
	// Specific to ABXD, usually unsafe
	'style'           => TRUE,
	'title'           => TRUE,
	'translate'       => 'process_yesno',
];

$filter_tags = [
	'a' => [
		'href'     => 'process_url',
		'target'   => 'process_target',
		'rel'      => TRUE,
		'hreflang' => TRUE,
		'media'    => TRUE,
		'type'     => TRUE,
		'name'     => 'process_depr_name',
	],
	'abbr' => [],
	'address' => [],
	'area' => [
		'alt'      => TRUE,
		'href'     => 'process_url',
		'target'   => 'process_target',
		'rel'      => TRUE,
		'media'    => TRUE,
		'hreflang' => TRUE,
		'type'     => TRUE,
		'shape'    => 'process_shape',
		'coords'   => TRUE,
	],
	'article' => [],
	'aside' => [],
	'audio' => [
		'controls'  => 'process_null_attr',
		'loop'      => 'process_null_attr',
		'muted'     => 'process_null_attr',
		'src'       => 'process_url',
	],
	'b' => [],
	'bdi' => [],
	'bdo' => [],
	'blockquote' => [
		'cite' => 'process_url',
	],
	'br' => [],
	'button' => [
		'disabled' => 'process_null_attr',
	],
	'caption' => [
		'align' => 'process_depr_caption_align',
	],
	'cite' => [],
	'code' => [],
	'col' => [
		'span'  => 'process_int',
		'width' => 'process_depr_width',
	],
	'colgroup' => [
		'span'  => 'process_int',
		'width' => 'process_depr_width',
	],
	'dd' => [],
	'del' => [
		'cite'     => 'process_url',
		'datetime' => TRUE,
	],
	// Perhaps it should be dynamically changed into [spoiler] tag
	'details' => [
		'open' => 'process_null_attr',
	],
	'dfn' => [],
	'div' => [
		'align' => 'process_depr_align',
	],
	// compact doesn't work anyway in any browser
	'dl' => [],
	'dt' => [],
	'em' => [],
	'fieldset' => [
		'disabled' => 'process_null_attr',
	],
	'figcaption' => [],
	'figure' => [],
	'footer' => [],
	'h1' => [
		'align' => 'process_depr_align',
	],
	'h2' => [
		'align' => 'process_depr_align',
	],
	'h3' => [
		'align' => 'process_depr_align',
	],
	'h4' => [
		'align' => 'process_depr_align',
	],
	'h5' => [
		'align' => 'process_depr_align',
	],
	'h6' => [
		'align' => 'process_depr_align',
	],
	'header' => [],
	'hr' => [
		'align' => 'process_depr_align',
		'width' => 'process_depr_width',
	],
	'i' => [],
	'img' => [
		'src'    => 'process_url',
		'alt'    => TRUE,
		'height' => 'process_int',
		'width'  => 'process_int',
		'usemap' => TRUE,
		'ismap'  => 'process_null_attr',
		'border' => 'process_depr_border',
		'name'   => 'process_depr_name',
		'align'  => 'process_depr_float',
	],
	'input' => [
		'type'        => TRUE,
		'disabled'    => 'process_null_attr',
		'maxlength'   => 'process_int',
		'readonly'    => 'process_null_attr',
		'size'        => 'process_int',
		'placeholder' => TRUE,
		'pattern'     => TRUE,
	],
	'ins' => [
		'cite'     => 'process_url',
		'datetime' => TRUE
	],
	'kbd' => [],
	'label' => [],
	'legend' => [],
	'li' => [
		'value' => TRUE,
	],
	'link' => [
		'href' => 'process_url',
	],
	'map' => [
		'name' => TRUE,
	],
	'mark' => [],
	'meter' => [
		'value'   => TRUE,
		'min'     => TRUE,
		'low'     => TRUE,
		'high'    => TRUE,
		'max'     => TRUE,
		'optimum' => TRUE,
	],
	'nav' => [],
	'ol' => [
		'start'    => TRUE,
		'reversed' => 'process_null_attr',
		'type'     => TRUE,
	],
	'optgroup' => [
		'label'    => TRUE,
		'disabled' => 'process_null_attr',
	],
	'option' => [
		'disabled' => 'process_null_attr',
		'selected' => 'process_null_attr',
		'label'    => TRUE,
		'value'    => TRUE,
	],
	'p' => [
		'align' => 'process_depr_align',
	],
	'pre' => [],
	'progress' => [
		'value' => TRUE,
		'max'   => TRUE,
	],
	'q' => [
		'cite' => 'process_url',
	],
	'rp' => [],
	'rt' => [],
	'ruby' => [],
	's' => [],
	'samp' => [],
	'section' => [],
	'select' => [
		'disabled' => 'process_null_attr',
		'size'     => 'process_int',
		'multiple' => 'process_null_attr',
	],
	'small' => [],
	'span' => [],
	'strong' => [],
	// AcmlmBoard magic
	'style' => [
		'media'  => TRUE,
		'scoped' => 'process_null_attr',
	],
	'sub' => [],
	'summary' => [],
	'sup' => [],
	// TODO: Fill in deprecated table attributes
	'table' => [
		// Non layout
		'border' => 'process_int',
	],
	'tbody' => [],
	'td' => [
		'width'   => 'process_depr_width',
		'colspan' => 'process_int',
		'rowspan' => 'process_int',
	],
	'textarea' => [
		'disabled'    => 'process_null_attr',
		'placeholder' => TRUE,
		'rows'        => 'process_int',
		'cols'        => 'process_int',
		'wrap'        => TRUE,
		'readonly'    => 'process_null_attr',
	],
	'tfoot' => [],
	'th' => [
		'width'   => 'process_depr_width',
		'colspan' => 'process_int',
		'rowspan' => 'process_int',
		'scope'   => TRUE,
	],
	'thead' => [],
	'time' => [
		'datetime' => TRUE,
	],
	'tr' => [],
	'u' => [],
	'ul' => [],
	'var' => [],
	'video' => [
		'src'      => 'process_url',
		'muted'    => 'process_null_attr',
		'height'   => 'process_int',
		'width'    => 'process_int',
		'poster'   => 'process_url',
		'loop'     => 'process_null_attr',
		'controls' => 'process_null_attr',
	],
	'wbr' => [],
];

$filter_mandatory = [
	'button' => [
		// Buttons are fine, provided they don't work
		// [insert trollface here]
		'type' => 'button',
	],
	'img' => [
		'src' => 'about:blank',
	],
	'input' => [
		'autocomplete' => 'off',
	],
	'link' => [
		'rel' => 'stylesheet',
	],
];

function cleanUpPost($postText, $name="", $noSmilies = false) {
	global $filter_tags, $bbcode, $postNoSmilies;
	$postNoSmilies = $noSmilies;
	require_once 'HTML5/Parser.php';
	$document = HTML5_Parser::parseFragment($postText, null, null, $filter_tags, $bbcode, $name)->item(0)->ownerDocument;
	// The DOM tree is empty. Ignore it.
	if (!$document) return "";
	process($document);
	return $document->saveHTML();
}

function process(DOMNode $current_node) {
	global $filter_args, $filter_tags, $filter_mandatory, $bbcode;
	if ($current_node instanceof DOMElement || $current_node instanceof DOMDocument) {
		// Recursion. I need iterator_to_array(), because it's likely
		// that node list will be modified.
		foreach (iterator_to_array($current_node->childNodes) as $node)
			process($node);

		// BBCode hack is NOT allowed to exist
		if ($current_node->tagName === 'bbcodehack') {
			$value = $current_node->hasAttribute('value') ? $current_node->getAttribute('value') : NULL;
			$nodes = $current_node->hasAttribute('pre')   ? $current_node->getAttribute('pre')   : $current_node->childNodes;
			$nodes = $bbcode[$current_node->getAttribute('name')]['callback']($current_node->ownerDocument, $nodes, $value, [
				'borked' => $current_node->hasAttribute('borked'),
			]);
			if (!is_array($nodes)) {
				$nodes = [$nodes];
			}
			foreach ($nodes as $node)
				$current_node->parentNode->insertBefore($node, $current_node);

			// Remove bbcodehack from DOM
			$current_node->parentNode->removeChild($current_node);
		}
		// Move node below when invalid.
		elseif ($current_node->tagName && !isset($filter_tags[$current_node->tagName])) {
			while ($current_node->hasChildNodes())
				$current_node->parentNode->insertBefore($current_node->childNodes->item(0), $current_node);
			$current_node->parentNode->removeChild($current_node);
		}
		// Check every attribute, and remove it when unknown.
		else {
			if ($current_node->hasAttributes())
				// I need iterator_to_array, as I modify attributes
				// list while iterating.
				foreach (iterator_to_array($current_node->attributes) as $attr) {
					$attribute = isset($filter_tags[$current_node->tagName][$attr->name])
						? $filter_tags[$current_node->tagName][$attr->name]
						: (isset($filter_args[$attr->name])
							? $filter_args[$attr->name]
							: NULL);

					if (!$attribute)
						$current_node->removeAttribute($attr->name);
					elseif (!is_bool($attribute)) {
						$value = $attribute($attr->value, $current_node);
						if ($value === NULL)
							$current_node->removeAttribute($attr->name);
						else
							$current_node->setAttribute($attr->name, $value);
					}
				}

			if (isset($filter_mandatory[$current_node->tagName]))
				foreach ($filter_mandatory[$current_node->tagName] as $attr => $value)
					if (!$current_node->hasAttribute($attr))
						$current_node->setAttribute($attr, $value);
		}
	}
	elseif ($current_node instanceof DOMComment) {
		// Unsafe because of conditional comments
		$current_node->parentNode->removeChild($current_node);
	}
}

function add_css($css, $node) {
	$node->setAttribute('style', $node->getAttribute('style') . ';' . $css);
}

function process_url($url) {
	$url = preg_replace('/\s/', "", $url);
	// The DOM unescapes meta characters, so simply checking for
	// javascript: should be safe.
	return stripos($url, 'javascript:') === FALSE ? $url : NULL;
}

function process_target($target) {
	// Only target _blank is useful, really.
	return $target === "" ? NULL : "_blank";
}

function process_dir($direction) {
	$directions = [
		'ltr' => TRUE, 'rtl' => TRUE, 'auto' => TRUE,
	];
	return isset($directions[$direction]) ? $direction : NULL;
}

function process_null_attr() {
	return "";
}

// Make sure IDs are unique
function process_id($id) {
	static $ids;
	if (isset($ids[$id]))
		return NULL;
	else {
		$ids[$id] = TRUE;
		return $id;
	}
}

function process_truefalse($value) {
	$values = [
		'true' => TRUE, 'false' => TRUE,
	];
	return isset($values[$value]) ? $value : NULL;
}

function process_yesno($value) {
	$values = [
		'yes' => TRUE, 'no' => TRUE,
	];
	return isset($values[$value]) ? $value : NULL;
}

function process_shape($shape) {
	$shapes = [
		'rect' => 'rect', 'circ' => 'circle',
		'circle' => 'circle', 'poly' => 'poly',
		'default' => 'default',
	];
	return isset($shapes[$shape]) ? $shapes[$shape] : NULL;
}

function process_depr_caption_align($value, $node) {
	$replacements = [
		'left' => 'text-align: left',
		'right' => 'text-align: right',
		'top' => 'caption-side: top',
		'bottom' => 'caption-side: bottom',
	];
	if (isset($replacements[$value]))
		add_css($replacements[$value], $node);
}

function process_depr_name($value, $node) {
	if (!$node->hasAttribute('id'))
		$node->setAttribute('id', $value);
}

function process_int($int) {
	return min(0, (int) $int);
}

function process_depr_width($width, $node) {
	$width = min((float) $width);
	add_css("width: ${width}px", $node);
}

function process_depr_border($width, $node) {
	$width = min(0, (float) $width);
	add_css("border-width: ${width}px", $node);
}

function process_depr_align($direction, $node) {
	$directions = [
		'left' => TRUE, 'right' => TRUE,
		'center' => TRUE, 'justify' => TRUE,
	];
	if (isset($directions[$direction]))
		add_css("text-align: $direction", $node);
}

function process_depr_float($direction, $node) {
	$directions = [
		'left' => TRUE, 'right' => TRUE,
	];
	if (isset($directions[$direction]))
		add_css("float: $direction", $node);
}
