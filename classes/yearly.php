<?php

class FacetWP_Facet_Yearly {

	function __construct() {
		$this->label = __( 'Yearly Archive', 'fwp' );
	}

	/**
	 * Load the available choices
	 */
	function load_values( $params ) {
		global $wpdb;

		$facet = $params['facet'];

		// Where
		$where_clause = $params['where_clause'];

		// Orderby
		$orderby = 'f.facet_display_value DESC';
		if ( ! empty( $facet['orderby'] ) && 'asc' === $facet['orderby'] ) {
			$orderby = 'f.facet_display_value ASC';
		}

		// Limit
		$limit = 10;
		if ( ! empty( $facet['count'] ) && absint( $facet['count'] ) > 0 ) {
			$limit = absint( $facet['count'] );
		}

		$orderby      = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );
		$where_clause = apply_filters( 'facetwp_facet_where', $where_clause, $facet );

		$sql = "
		SELECT DATE_FORMAT(f.facet_value, '%Y') as facet_value, f.facet_display_value, COUNT(*) AS counter
		FROM {$wpdb->prefix}facetwp_index f
		WHERE f.facet_name = '{$facet['name']}' $where_clause
		GROUP BY DATE_FORMAT(f.facet_value, '%Y')
		ORDER BY $orderby
		LIMIT $limit";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Generate the facet HTML
	 */
	function render( $params ) {

		$output          = '';
		$facet           = $params['facet'];
		$values          = (array) $params['values'];
		$selected_values = (array) $params['selected_values'];

		$label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp' ) : sprintf( __( '%s', 'fwp' ), $facet['label_any'] );

		// Setting classes for the select element.
		$select_classes = empty( $selected_values ) ? 'facetwp-yearly facetwp-yearly-default' : 'facetwp-yearly';

		// Building select HTML element for the facet backend.
		$output .= '<select class="' . $select_classes . '">';
		$output .= sprintf( '<option value="">%s</option>', esc_html( $label_any ) );

		foreach ( $values as $result ) {
			$selected = in_array( $result['facet_value'], $selected_values ) ? ' selected' : '';

			$display_value = date_i18n( 'Y', strtotime( $result['facet_display_value'] ) );
			// Determine whether to show counts
			$show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, $params );
			if ( $show_counts ) {
				$display_value .= sprintf( ' (%s)', $result['counter'] );
			}

			$output .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $result['facet_value'] ),
				$selected,
				esc_html( $display_value )
			);
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Filter the query based on selected values
	 */
	function filter_posts( $params ) {
		global $wpdb;

		$output          = array();
		$facet           = $params['facet'];
		$selected_values = $params['selected_values'];

		$sql = $wpdb->prepare( "SELECT DISTINCT post_id
			FROM {$wpdb->prefix}facetwp_index
			WHERE facet_name = %s
			AND YEAR(facet_value) = %d
			ORDER BY facet_value DESC",
			$facet['name'],
			absint( $selected_values )
		);

		$output = $wpdb->get_col( $sql );

		return $output;
	}

	/**
	 * Output admin settings HTML
	 */
	function settings_html() { ?>
		<div class="facetwp-row">
			<div>
				<?php _e( 'Default label', 'fwp' ); ?>:
				<div class="facetwp-tooltip">
					<span class="icon-question">?</span>
					<div class="facetwp-tooltip-content">
						<?php _e( 'Customize the first option label (default: "Any")', 'fwp' ); ?>
					</div>
				</div>
			</div>
			<div><input type="text" class="facet-label-any" value="<?php _e( 'Any', 'fwp' ); ?>"/></div>
		</div>
		<div class="facetwp-row">
			<div>
				<?php _e( 'Archive order', 'fwp' ); ?>:
				<div class="facetwp-tooltip">
					<span class="icon-question">?</span>
					<div class="facetwp-tooltip-content">
						<?php _e( 'Customize the archives order (default: "Newest to Oldest")', 'fwp' ); ?>
					</div>
				</div>
			</div>
			<div>
				<select class="facet-orderby">
					<option value="desc" selected><?php _e( 'Newest to Oldest', 'fwp' ); ?></option>
					<option value="asc"><?php _e( 'Oldest to newest', 'fwp' ); ?></option>
				</select>
			</div>
		</div>
		<div class="facetwp-row">
			<div>
				<?php _e( 'Count', 'fwp' ); ?>:
				<div class="facetwp-tooltip">
					<span class="icon-question">?</span>
					<div class="facetwp-tooltip-content">
						<?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?>
					</div>
				</div>
			</div>
			<div><input type="text" class="facet-count" value="10"/></div>
		</div>
	<?php }

	/**
	 * Output any admin scripts
	 */
	function admin_scripts() { ?>
		<script>
			(function ($) {
				FWP.hooks.addAction('facetwp/load/yearly', function ($this, obj) {
					$this.find('.facet-source').val(obj['source']);
					$this.find('.type-yearly .facet-label-any').val(obj['label_any']);
					$this.find('.type-yearly .facet-orderby').val(obj['orderby']);
					$this.find('.type-yearly .facet-count').val(obj['count']);
				});

				FWP.hooks.addFilter('facetwp/save/yearly', function ($this, obj) {
					obj['source'] = $this.find('.facet-source').val();
					obj['label_any'] = $this.find('.type-yearly .facet-label-any').val();
					obj['orderby'] = $this.find('.type-yearly .facet-orderby').val();
					obj['count'] = $this.find('.type-yearly .facet-count').val();
					return obj;
				});
			})(jQuery);
		</script>
	<?php }

	/**
	 * Output any front-end scripts
	 */
	function front_scripts() { ?>
		<script>
			(function ($) {
				FWP.hooks.addAction('facetwp/refresh/yearly', function ($this, facet_name) {
					var val = $this.find('.facetwp-yearly').val();
					FWP.facets[facet_name] = val ? [val] : [];
				});

				FWP.hooks.addAction('facetwp/ready', function () {
					$(document).on('change', '.facetwp-facet .facetwp-yearly', function () {
						var $facet = $(this).closest('.facetwp-facet');
						var isDefault = $facet.find(':selected').val() === '';
						if (!isDefault) FWP.static_facet = $facet.attr('data-name');
						$facet.find('select').toggleClass('facetwp-yearly-default', isDefault)
						FWP.autoload();
					});
				});
			})(jQuery);
		</script>
	<?php }
}
