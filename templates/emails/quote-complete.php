<?php
/**
 * Quote Complete Email
 *
 * @package Quotes for WooCommerce/Email Templates
 */

?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
if ( $order ) :
	$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) ? $order->billing_first_name : $order->get_billing_first_name();
	?>
	<p>
		<?php
		// translators: Billing First Name.
		echo sprintf( esc_html__( 'Hola %s', 'quote-wc' ), esc_attr( $billing_first_name ) );
		?>
	</p>
<?php endif; ?>

<p>
	<?php
	// translators: Order Number.
	echo sprintf( esc_html__( 'Tu solicitud de cotización #%s ha sido Aprobada.', 'quote-wc' ), esc_html( $order->get_order_number() ) );
	?>
</p>

<?php if ( $order ) : ?>

	<?php
	// Obtener los productos del pedido
	$items = $order->get_items();
	if ( ! empty( $items ) ) :
		foreach ( $items as $item ) {
			$product = $item->get_product();
			$product_name = $item->get_name();
			$product_url = '';
			
			// Si es una variación, obtener el producto padre y construir la URL con atributos
			if ( $product && $product->is_type( 'variation' ) ) {
				$parent_id = $product->get_parent_id();
				$parent_product = wc_get_product( $parent_id );
				
				if ( $parent_product ) {
					$product_url = get_permalink( $parent_id );
					
					// Obtener los atributos de la variación
					$variation_attributes = $product->get_variation_attributes();
					
					if ( ! empty( $variation_attributes ) ) {
						$query_params = array();
						
						foreach ( $variation_attributes as $attribute_name => $attribute_value ) {
							// Convertir attribute_pa_color a attribute_color para la URL
							$clean_attribute_name = str_replace( 'attribute_', '', $attribute_name );
							$query_params[ $clean_attribute_name ] = $attribute_value;
						}
						
						// Añadir los parámetros a la URL
						if ( ! empty( $query_params ) ) {
							$product_url = add_query_arg( $query_params, $product_url );
						}
					}
				}
			} elseif ( $product ) {
				// Si es un producto simple
				$product_url = get_permalink( $product->get_id() );
			}
			
			?>
			<p>
				<?php
				if ( ! empty( $product_url ) ) {
					// translators: Product name with link.
					echo sprintf( 
						esc_html__( 'Realiza el envío del artículo %s debidamente protegido a:', 'quote-wc' ), 
						'<strong><a href="' . esc_url( $product_url ) . '" style="color: #0071a1; text-decoration: none;">' . esc_html( $product_name ) . '</a></strong>'
					);
				} else {
					// translators: Product name without link.
					echo sprintf( 
						esc_html__( 'Realiza el envío del artículo %s debidamente protegido a:', 'quote-wc' ), 
						'<strong>' . esc_html( $product_name ) . '</strong>'
					);
				}
				?>
			</p>
			<?php
			// Solo mostramos el primer producto ya que mencionaste que solo habrá uno
			break;
		}
	endif;
	?>

	<table cellspacing="0" cellpadding="8" style="width: 100%; background-color: #f7f7f7; border: 1px solid #ddd; margin: 20px 0;">
		<tr>
			<td style="padding: 15px;">
				<p style="margin: 0 0 10px 0;">
					<strong><?php esc_html_e( 'Destinatario:', 'quote-wc' ); ?></strong> 
					<?php echo esc_html( apply_filters( 'qwc_shipping_recipient_name', 'CHOLLOMOVILES BYC SL' ) ); ?>
				</p>
				<p style="margin: 0 0 10px 0;">
					<strong><?php esc_html_e( 'Dirección:', 'quote-wc' ); ?></strong> 
					<?php echo esc_html( apply_filters( 'qwc_shipping_address', 'Calle Comercio, 87. La Puebla del Río 41130 Sevilla' ) ); ?>
				</p>
				<p style="margin: 0;">
					<strong><?php esc_html_e( 'Teléfono:', 'quote-wc' ); ?></strong> 
					<?php echo esc_html( apply_filters( 'qwc_shipping_phone', '613 132 447' ) ); ?>
				</p>
			</td>
		</tr>
	</table>

	<p>
		<?php esc_html_e( 'Nuestros técnicos lo revisarán para confirmar que su estado corresponde con las características descritas en tu solicitud de venta.', 'quote-wc' ); ?>
	</p>

	<p>
		<strong><?php esc_html_e( 'Recibe tu pago:', 'quote-wc' ); ?></strong> 
		<?php esc_html_e( 'Si todo es correcto te pediremos una foto de tu DNI/NIF/CIF/NIE para formalizar el contrato. Una vez que lo recibas y lo firmes, ¡en menos de 48 horas tendrás tu pago y el recibo listos! ¡Fácil, rápido y sin complicaciones!', 'quote-wc' ); ?>
	</p>

	<?php
	// Opcional: Si quieres mostrar los detalles del pedido completos
	$show_order_details = apply_filters( 'qwc_quote_complete_show_order_details', false );
	
	if ( $show_order_details ) :
		?>
		<hr style="border: 0; border-top: 1px solid #ddd; margin: 30px 0;">
		
		<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

		<?php
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
			$order_date = $order->order_date;
		} else {
			$order_post = get_post( $order_details->order_id );
			$post_date  = strtotime( $order_post->post_date );
			$order_date = date( 'Y-m-d H:i:s', $post_date ); //phpcs:ignore
		}
		?>
		
		<h3><?php echo esc_html__( 'Detalles del pedido', 'quote-wc' ); ?></h3>
		
		<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
			<thead>
				<tr>
					<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Product', 'quote-wc' ); ?></th>
					<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Quantity', 'quote-wc' ); ?></th>
					<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Price', 'quote-wc' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$downloadable = $order->is_download_permitted();

				switch ( $order->get_status() ) {
					case 'completed':
						$args = array(
							'show_download_links' => $downloadable,
							'show_sku'            => false,
							'show_purchase_note'  => true,
						);
						if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
							echo wp_kses_post( $order->email_order_items_table( $args ) );
						} else {
							echo wp_kses_post( wc_get_email_order_items( $order, $args ) );
						}
						break;
					case 'processing':
						$args = array(
							'show_download_links' => $downloadable,
							'show_sku'            => true,
							'show_purchase_note'  => true,
						);
						if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
							echo wp_kses_post( $order->email_order_items_table( $args ) );
						} else {
							echo wp_kses_post( wc_get_email_order_items( $order, $args ) );
						}
						break;
					default:
						$args = array(
							'show_download_links' => $downloadable,
							'show_sku'            => true,
							'show_purchase_note'  => false,
						);
						if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0' ) < 0 ) {
							echo wp_kses_post( $order->email_order_items_table( $args ) );
						} else {
							echo wp_kses_post( wc_get_email_order_items( $order, $args ) );
						}
						break;
				}
				?>
			</tbody>
			<tfoot>
				<?php
				if ( $order->get_order_item_totals() ) {
					$i = 0;
					foreach ( $order->get_order_item_totals() as $total ) {
						$i++;
						?>
							<tr>
								<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; 
								<?php
								if ( 1 === $i ) {
									echo 'border-top-width: 4px;';}
								?>
								"><?php echo esc_html( $total['label'] ); ?></th>
								<td style="text-align:left; border: 1px solid #eee; 
								<?php
								if ( 1 === $i ) {
									echo 'border-top-width: 4px;';
								}
								?>
								"><?php echo wp_kses_post( $total['value'] ); ?></td>
							</tr>
							<?php
					}
				}
				?>
			</tfoot>
		</table>

		<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

		<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>
	<?php endif; ?>

<?php endif; ?>

<p style="margin-top: 30px;">
	<?php esc_html_e( 'Gracias de parte del equipo.', 'quote-wc' ); ?>
</p>

<?php do_action( 'woocommerce_email_footer' ); ?>