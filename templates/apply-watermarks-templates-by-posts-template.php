<?php
use GPLSCore\GPLS_PLUGIN_WMFW\Apply_Watermarks_Queries;

defined( 'ABSPATH' ) || exit();

$cpts = Apply_Watermarks_Queries::get_Cpts();
foreach ( $cpts as $cpt_slug ) :
	$cpt_obj   = get_post_type_object( $cpt_slug );
	$cpt_count = Apply_Watermarks_Queries::get_cpt_count( $cpt_slug );
	?>
	<div class="cpt-name-row my-5">
		<label>
			<input type="checkbox" name="cpt_name[]" class="cpt-name-checkbox cpt-name-checkbox-<?php echo esc_attr( $cpt_slug ); ?>" value="<?php echo esc_attr( $cpt_slug ); ?>" >
			<?php echo esc_html( $cpt_obj->label ); ?>
			<?php echo esc_attr( '( ' . $cpt_count . ' )' ); ?>
		</label>
		<?php
		if ( 0 == $cpt_count ) :
			?>
			<div class="subtitle w-100 d-inline-block" >
				<?php printf( esc_html__( 'No %s yet:', 'watermark-images-for-wp-and-woo-grandpluginswp' ), $cpt_obj->label ); ?>
			</div>
			<?php
		else :
			?>
		<div class="subtitle select-images-by-post-type-<?php echo esc_attr( $cpt_slug ); ?>-wrapper collapse border m-3 p-4" id="accordion-wrapper-<?php echo esc_attr( $cpt_slug ); ?>">
			<div class="accordion" id="accordion-<?php echo esc_attr( $cpt_slug ); ?>">
				<div class="select-by-specific w-100">
					<div class="card-header">
						<h6 class="m-0">
							<label class="m-0">
								<input type="hidden" data-cpt_type="<?php echo esc_attr($cpt_slug ); ?>" class="cpt_select_specific_posts cpt_select_specific_posts_<?php echo esc_attr( $cpt_slug ); ?>" name="cpt_select_specific_posts[<?php echo esc_attr( $cpt_slug ); ?>]">
								<input id="heading-<?php echo esc_attr( $cpt_slug ); ?>-specific" class="cpt-select-type-radio" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#select-by-specific-<?php echo esc_attr( $cpt_slug ); ?>" aria-controls="select-by-specific-<?php echo esc_attr( $cpt_slug ); ?>" type="radio" name="cpt_select_type[<?php echo esc_attr( $cpt_slug ); ?>]" value="1" checked>
								<?php esc_html_e( 'Select posts: ', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
							</label>
						</h6>
					</div>
					<div id="select-by-specific-<?php echo esc_attr( $cpt_slug ); ?>" class="accordion-collapse collapse show" aria-labelledby="heading-<?php echo esc_attr( $cpt_slug ); ?>-specific" data-bs-parent="#accordion-<?php echo esc_attr( $cpt_slug ); ?>">
						<div class="card-body">
							<button data-bs-toggle="modal" data-bs-target="#modal-cpt-posts-<?php echo esc_attr( $cpt_slug ); ?>" type="button" class="button button-primary fire-specific-posts-modal"><?php esc_html_e( 'Select', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></button>
						</div>
					</div>
				</div>
				<div class="select-by-filters w-100">
					<div class="card-header" >
						<h6 class="m-0">
							<label class="m-0">
								<input id="heading-<?php echo esc_attr( $cpt_slug ); ?>-filters" class="cpt-select-type-radio" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" data-bs-toggle="collapse" data-bs-target="#select-by-filters-<?php echo esc_attr( $cpt_slug ); ?>" aria-expanded="false" aria-controls="select-by-filters-<?php echo esc_attr( $cpt_slug ); ?>" type="radio" name="cpt_select_type[<?php echo esc_attr( $cpt_slug ); ?>]" value="2">
								<?php esc_html_e( 'Select posts by filters: ', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?>
							</label>
						</h6>
					</div>
					<div id="select-by-filters-<?php echo esc_attr( $cpt_slug ); ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo esc_attr( $cpt_slug ); ?>-filters" data-bs-parent="#accordion-<?php echo esc_attr( $cpt_slug ); ?>">
						<div class="card-body">
							<!-- Status Filter -->
							<div class="d-inline-block">
								<p class="ps-0 nonessential column-name text-primary"><small><?php esc_html_e( '( Any of )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>&nbsp;<?php echo esc_html__( 'Available Status:', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></p>
								<select class="cpt-statuses-select" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" name="cpt_statuses[<?php echo esc_attr( $cpt_slug ); ?>][]" multiple>
									<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
									<?php
									$post_statuses = Apply_Watermarks_Queries::get_cpt_statuses( $cpt_slug );
									foreach ( $post_statuses as $status_name ) :
										?>
										<option value="<?php echo esc_attr( $status_name ); ?>"><?php echo esc_attr( $status_name ); ?></option>
										<?php
									endforeach;
									?>
								</select>
							</div>
							<div class="relation d-inline-block mx-2"><?php esc_html_e( 'and' ); ?></div>
							<!-- Date Filter -->
							<div class="d-inline-block ms-3 align-middle pb-3">
								<fieldset>
									<div class="block mb-3">
										<legend class="screen-reader-text"><?php esc_html_e( 'Date range' ); ?></legend>
										<p for="post-start-date" class="label-responsive me-1 d-block"><?php esc_html_e( 'Start date:' ); ?></p>
										<select class="cpt-start-date-select" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" name="cpt_start_date[<?php echo esc_attr( $cpt_slug ); ?>]">
											<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
											<?php Apply_Watermarks_Queries::select_date_options( $cpt_slug ); ?>
										</select>
									</div>
									<div class="block">
										<p for="post-end-date" class="label-responsive me-2 d-block"><?php esc_html_e( 'End date:' ); ?></p>
										<select class="cpt-end-date-select" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" name="cpt_end_date[<?php echo esc_attr( $cpt_slug ); ?>]">
											<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
											<?php Apply_Watermarks_Queries::select_date_options( $cpt_slug ); ?>
										</select>
									</div>
								</fieldset>
							</div>
							<div class="relation d-inline-block mx-2"><?php esc_html_e( 'and' ); ?></div>
							<!-- Authors Filter -->
							<div class="d-inline-block ms-3">
								<p class="ps-0 nonessential column-name text-primary"><small><?php esc_html_e( '( Any of )', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></small>&nbsp;<?php esc_html_e( 'Authors ' ); ?></p>
								<?php Apply_Watermarks_Queries::select_authors_options( $cpt_slug ); ?>
							</div>
							<!-- Terms Filter -->
							<?php
							$cpt_taxonomies = get_object_taxonomies( $cpt_slug, 'names' );
							if ( ! empty( $cpt_taxonomies ) ) :
								foreach ( $cpt_taxonomies as $cpt_taxonomy_name ) :
									$taxonomy_obj = get_taxonomy( $cpt_taxonomy_name );
									if ( ! empty( $cpt_taxonomy_name ) ) :
										?>
										<div class="relation d-inline-block mx-2"><?php esc_html_e( 'and' ); ?></div>
									   <div class="ms-2 d-inline-block" >
											<p class="ps-0 nonessential column-name text-primary"><?php echo esc_attr( $taxonomy_obj->label . ' [ ' . $cpt_taxonomy_name . ' ] ' ); ?></p>
											<select class="cpt-terms-select" data-cpt_type="<?php echo esc_attr( $cpt_slug ); ?>" data-tax_name="<?php echo esc_attr( $cpt_taxonomy_name ); ?>" name="cpt_name_terms[<?php echo esc_attr( $cpt_slug ); ?>][<?php echo esc_attr( $cpt_taxonomy_name ); ?>]" id="<?php echo esc_attr( 'cpt-name-terms-select-' . $cpt_slug . '-' . $cpt_taxonomy_name ); ?>"></select>
										</div>
										<?php
									endif;
								endforeach;
							endif;
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div class="modal specific-posts-modal fade bulk-apply-modal" id="modal-cpt-posts-<?php echo esc_attr( $cpt_slug ); ?>" tabindex="-1" role="dialog" aria-hidden="true" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>">
		<div class="modal-dialog modal-fullscreen mx-auto">
			<div class="modal-content">
				<div class="modal-body accordion">
					<div class="row">
						<div class="col-md-3">
							<div class="list-group" role="tablist">
								<a data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" id="tab-cpt-all-<?php echo esc_attr( $cpt_slug ); ?>" class="select-list-all-tab list-group-item list-group-item-action active p-4" data-bs-toggle="list" role="tab" href="#all-posts-<?php echo esc_attr( $cpt_slug ); ?>"><?php esc_html_e( 'All Posts', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></a>
								<a data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" id="tab-cpt-selected-<?php echo esc_attr( $cpt_slug ); ?>" class="select-list-selected-tab list-group-item list-group-item-action p-4" data-bs-toggle="list" role="tab" href="#selected-posts-<?php echo esc_attr( $cpt_slug ); ?>"><?php esc_html_e( 'Selected Posts', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></a>
							</div>
						</div>
						<div class="col-md-9 tab-content">
							<!-- Select CPT posts Wrapper -->
							<div id="selected-posts-<?php echo esc_attr( $cpt_slug ); ?>" aria-labelledby="tab-cpt-selected-<?php echo esc_attr( $cpt_slug ); ?>" class="gpls-selected-posts-list-wrapper tab-pane fade">
								<div class="modal-header">
									<div class="title d-inline-block-float-left">
										<h5><?php esc_html_e( 'Selected Posts', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
									</div>
									<div class="modal-close d-inline-block float-right">
										<button class="button close-modal-btn d-flex align-items-center">
											<span class="dashicons dashicons-dismiss"></span>
										</button>
									</div>
								</div>
								<table class="wp-list-table wp-list-selected-table widefat fixed striped posts">
									<thead>
										<tr>
											<th scope="col" id="title-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-title column-primary">
												<span><?php esc_html_e( 'Title', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											</th>
											<th scope="col" id="date-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-date">
												<span><?php esc_html_e( 'Date', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											</th>
											<th scope="col" id="actions-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-actions">
												<span><?php esc_html_e( 'Actions', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<!-- All CPT posts Wrapper -->
							<div id="all-posts-<?php echo esc_attr( $cpt_slug ); ?>" aria-labelledby="tab-cpt-all-<?php echo esc_attr( $cpt_slug ); ?>" class="gpls-all-posts-list-wrapper tab-pane position-relative overflow-hidden show active">
								<?php Apply_Watermarks_Queries::loader_html(); ?>
								<div class="modal-header">
									<div class="title d-inline-block-float-left">
										<h5><?php esc_html_e( 'Select posts to search in', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
									</div>
									<div class="modal-close d-inline-block float-right">
										<button class="button close-modal-btn d-flex align-items-center">
											<span class="dashicons dashicons-dismiss"></span>
										</button>
									</div>
								</div>
								<div class="actions overflow-hidden">
									<?php
									if ( $cpt_count > 20 ) :
										$full_pages = ceil( $cpt_count / 20 );
										?>
									<div id="all-posts-pagination-<?php echo esc_attr( $cpt_slug ); ?>" class="my-3 float-right all-cpt-posts-pagination">
										<div class="tablenav-pages m-0 float-left">
											<span class="float-left displaying-num p-1"><?php echo esc_attr( $cpt_count ); ?> <?php esc_html_e( 'items', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											<span class="float-left pagination-links">
												<button type="button" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" class="btn btn-primary float-left me-1 first-page button disabled" data-paged="1"><span>&#8606;</span></button>
												<button type="button" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" class="btn btn-primary float-left me-1 prev-page button disabled" data-paged="1"><span>&#8592;</span></button>
												<span class="float-left me-1 paging-input">
                                                    <span id="current-page-selector-<?php echo esc_attr( $cpt_slug ); ?>" class="float-left p-1 current-page">1</span>
													<span class="float-left tablenav-paging-text">
														<span class="float-left p-1"> <?php esc_html( 'of' ); ?> </span>
														<span class="float-left total-pages p-1" data-pages="<?php echo esc_attr( $full_pages ); ?>"><?php echo esc_html( $full_pages ); ?></span>
													</span>
												</span>
												<button type="button" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" class="btn btn-primary float-left me-1 next-page button <?php echo esc_attr( ( $full_pages <= 1 ) ? 'disabled' : '' ); ?>" data-paged="2"><span>&#8594;</span></button>
												<button type="button" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" class="btn btn-primary float-left me-1 last-page button <?php echo esc_attr( ( $full_pages <= 1 ) ? 'disabled' : '' ); ?>" data-paged="<?php echo esc_attr( $full_pages ); ?>"><span>&#8608;</span></button>
											</span>
										</div>
									</div>
									<?php endif; ?>
								</div>
								<table class="wp-list-table wp-list-select-posts-table widefat fixed striped posts">
									<thead>
										<tr>
											<td id="cb-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-cb check-column">
												<label for="cb-select-all-1-<?php echo esc_attr( $cpt_slug ); ?>" for="cb-select-all-1-<?php echo esc_attr( $cpt_slug ); ?>" class="screen-reader-text"><?php esc_html_e( 'Select All', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></label>
												<input type="checkbox" id="cb-select-all-1-<?php echo esc_attr( $cpt_slug ); ?>" class="cb-select-all-1">
											</td>
											<th scope="col" id="title-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-title column-primary">
												<span><?php esc_html_e( 'Title', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											</th>
											<th scope="col" id="date-<?php echo esc_attr( $cpt_slug ); ?>" class="manage-column column-date">
												<span><?php esc_html_e( 'Date', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
											</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endforeach; ?>

<!-- Selected Images Modal -->
<div class="modal selected-images-watermarks-template-modal fade bulk-apply-modal" id="modal-found-images-watermarks-template">
	<div class="modal-dialog modal-fullscreen mx-auto position-relative">
        <?php Apply_Watermarks_Queries::loader_html(); ?>
		<div class="modal-content">
			<div class="modal-header">
				<div class="title d-inline-block-float-left">
					<h5><?php esc_html_e( 'Images found in selected posts', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></h5>
				</div>
				<div class="modal-close d-inline-block float-right">
					<button class="button close-modal-btn d-flex align-items-center">
						<span class="dashicons dashicons-dismiss"></span>
					</button>
				</div>
			</div>
			<div class="modal-body">
                <div class="actions overflow-hidden">
                    <div id="all-found-images-pagination" class="my-3 float-right all-found-images-pagination">
                        <div class="tablenav-pages m-0">
                            <span class="float-left displaying-num p-1">1 <?php esc_html_e( 'item', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
                            <span class="float-left pagination-links">
                                <button type="button" class="btn btn-primary float-left me-1 first-page button disabled"  data-paged="1"><span>&#8606;</span></button>
                                <button type="button" class="btn btn-primary float-left me-1 prev-page button disabled"  data-paged="1"><span>&#8592;</span></button>
                                <span class="float-left me-1 paging-input">
                                <span class="float-left p-1 current-page">1</span>
                                    <!-- <input type="number" min="1" max="1" value="1" class="float-left current-page" size="3"> -->
                                    <span class="float-left tablenav-paging-text">
                                        <span class="float-left p-1"> <?php esc_html_e( 'of' ); ?> </span>
                                        <span class="float-left total-pages p-1" data-pages="1">1</span>
                                    </span>
                                </span>
                                <button type="button" class="btn btn-primary float-left me-1 next-page button disabled" data-paged="1"><span>&#8594;</span></button>
                                <button type="button" class="btn btn-primary float-left me-1 last-page button disabled" data-paged="1"><span>&#8608;</span></button>
                            </span>
                        </div>
                    </div>
                </div>
				<table class="wp-list-table widefat fixed striped media">
					<thead>
						<tr>
                            <td class="manage-column column-cb check-column">
								<label for="cb-select-all-1" for="cb-select-all-1" class="screen-reader-text"><?php esc_html_e( 'Select All', 'gpls-wmfw-watermark-image-for-wordpress'); ?></label>
								<input type="checkbox" id="cb-select-all-1" class="cb-select-all-1">
							</td>
							<th scope="col" class="manage-column column-title column-primary">
								<span><?php esc_html_e( 'File', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
							</th>
							<th scope="col" class="manage-column column-parent column-primary">
								<span><?php esc_html_e( 'Attached to', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
							</th>
							<th scope="col" class="manage-column column-date">
								<span><?php esc_html_e( 'Date', 'watermark-images-for-wp-and-woo-grandpluginswp' ); ?></span>
							</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
