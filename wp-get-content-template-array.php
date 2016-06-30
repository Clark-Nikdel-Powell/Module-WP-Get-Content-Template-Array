<?php
namespace CNP;
/**
 * get_content_template_array.
 *
 * Allows us to split up Loop content based on archive vs. singular views by
 * building an array of content templates to check using locate_template() in index.php.
 * For example, on an archive for a post type called "books," the expected return value is:
 * - content/book-archive.php
 * - content/book.php
 * - content/content.php
 *
 * For the singular book view, the result would be:
 * - content/book-singular.php
 * - content/book.php
 * - content/content.php
 *
 * Therefore, if a post type has the same content for both archive and singular, use
 * {$post_type}.php. If the archive content differs from the singular content significantly,
 * use {$post_type}-archive.php and {$post_type}-singular.php.
 *
 * @since 0.3.0
 *
 * @global WP_Post $post Global post object.
 *
 * @return array An array of templates to check using locate_template.
 */
function get_content_template_array() {

	// I used $post instead of the queried_object here because
	// we're trying to figure out which post content template to use.
	global $post;

	// Content.php is the fallback for every content template, so it gets added first
	$content_dir = 'content/';
	$templates   = [
		$content_dir . 'content.php'
	];

	$post_type = '';

	// Generic Templates
	if ( is_archive() || is_home() || is_search() ) {
		array_unshift( $templates, $content_dir . 'content-archive.php' );
	}
	if ( is_singular() ) {
		array_unshift( $templates, $content_dir . 'content-singular.php' );
	}
	if ( is_404() ) {
		$post_type = '404';
		array_unshift( $templates, $content_dir . '404.php' );
	}
	if ( is_search() ) {
		$post_type = 'search';
		array_unshift( $templates, $content_dir . 'search.php' );
	}

	// Post-Type Specific Templates
	if ( ! empty( $post ) ) {

		// The post type name forms the base of our file checks.
		$post_type              = $post->post_type;
		$post_type_path_default = $content_dir . $post_type . '.php';

		// Start by putting the post type name at the beginning of the array.
		array_unshift( $templates, $post_type_path_default );

		// is_archive() covers post type AND taxonomy archives. The post content will be loaded for both.
		if ( is_archive() || is_home() ) {
			$post_type_path_archive = $content_dir . $post_type . '-archive.php';

			// Prepends to array, doesn't return a variable.
			array_unshift( $templates, $post_type_path_archive );
		}
		// is_singular covers the single view of a post type.
		if ( is_singular() ) {
			$post_type_path_singular = $content_dir . $post_type . '-singular.php';

			array_unshift( $templates, $post_type_path_singular );
		}
	}
	// Run after Post-Type Specific so that it has priority.
	if ( is_front_page() ) {
		$post_type = 'front-page';
		array_unshift( $templates, $content_dir . 'front-page.php' );
	}

	/**
	 * cnp_get_content_template_array.
	 *
	 * Global filter named after the function so that there's less to remember.
	 *
	 * @since 0.3.0
	 *
	 * @param array $templates The array of templates.
	 */
	$templates = apply_filters( 'cnp_get_content_template_array', $templates );

	/**
	 * cnp_get_{$post_type}_template_array.
	 *
	 * Namespaced filter by post type for more granular control.
	 *
	 * @since 0.3.0
	 *
	 * @param array $templates The array of templates.
	 */
	if ( '' !== $post_type ) {
		$templates = apply_filters( 'cnp_get_' . $post_type . '_template_array', $templates );
	}

	return $templates;
}
