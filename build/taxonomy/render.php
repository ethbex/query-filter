<?php
if ( empty( $attributes['taxonomy'] ) ) {
	return;
}

$id = 'query-filter-' . wp_generate_uuid4();

$taxonomy   = get_taxonomy( $attributes['taxonomy'] );

if ( empty( $block->context['query']['inherit'] ) ) {
	$query_id = $block->context['queryId'] ?? 0;
	$query_var = sprintf( 'query-%d-%s', $query_id, $attributes['taxonomy'] );
	$page_var = isset( $block->context['queryId'] ) ? 'query-' . $block->context['queryId'] . '-page' : 'query-page';
	$base_url = remove_query_arg( [ $query_var, $page_var ] );
} else {
	$query_var = sprintf( 'query-%s', $attributes['taxonomy'] );
	$page_var = 'page';
	$base_url = str_replace( '/page/' . get_query_var( 'paged' ), '', remove_query_arg( [ $query_var, $page_var ] ) );
}

$terms    = get_terms(
        [
                'hide_empty' => true,
                'taxonomy'   => $attributes['taxonomy'],
                'number'     => 100,
        ]
);
$selected = wp_unslash( $_GET[ $query_var ] ?? '' );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return;
}
?>

<div <?php echo get_block_wrapper_attributes( [ 'class' => 'wp-block-query-filter' ] ); ?> data-wp-interactive="query-filter" data-wp-context="{}">
        <label class="wp-block-query-filter-post-type__label wp-block-query-filter__label<?php echo $attributes['showLabel'] ? '' : ' screen-reader-text' ?>" <?php echo empty( $attributes['displayAsList'] ) ? 'for="' . esc_attr( $id ) . '"' : ''; ?>>
                <?php echo esc_html( $attributes['label'] ?? $taxonomy->label ); ?>
        </label>
        <?php if ( empty( $attributes['displayAsList'] ) ) : ?>
                <select class="wp-block-query-filter-post-type__select wp-block-query-filter__select" id="<?php echo esc_attr( $id ); ?>" data-wp-on--change="actions.navigate">
                        <option value="<?php echo esc_attr( $base_url ); ?>"><?php echo esc_html( $attributes['emptyLabel'] ?: __( 'All', 'query-filter' ) ); ?></option>
                        <?php foreach ( $terms as $term ) : ?>
                                <option value="<?php echo esc_attr( add_query_arg( [ $query_var => $term->slug, $page_var => false ], $base_url ) ); ?>" <?php selected( $term->slug, $selected ); ?>><?php echo esc_html( $term->name ); ?></option>
                        <?php endforeach; ?>
                </select>
        <?php else : ?>
                <ul class="wp-block-query-filter-taxonomy__list wp-block-query-filter__list<?php echo $attributes['showBullets'] ? '' : ' no-bullets'; ?>">
                        <li><a href="<?php echo esc_url( $base_url ); ?>"<?php echo $selected ? '' : ' aria-current="true"'; ?>><?php echo esc_html( $attributes['emptyLabel'] ?: __( 'All', 'query-filter' ) ); ?></a></li>
                        <?php foreach ( $terms as $term ) : ?>
                                <li><a href="<?php echo esc_url( add_query_arg( [ $query_var => $term->slug, $page_var => false ], $base_url ) ); ?>"<?php echo $selected === $term->slug ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $term->name ); ?></a></li>
                        <?php endforeach; ?>
                </ul>
        <?php endif; ?>
</div>
