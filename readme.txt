=== Quotes for WooCommerce ===

Contributors: pinal.shah, mikelmarques
Tags: woocommerce, quotes, proposals, hide-price, request-a-quote, woocommerce-request-quote, quotations, rfq
Requires at least: 4.5
Tested up to: 6.4
Requires PHP: 7.0
Stable tag: 2.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Convierte tu tienda WooCommerce en un sistema de cotizaciones. Oculta precios, gestiona solicitudes y envía presupuestos personalizados a tus clientes.

== Description ==

**Quotes for WooCommerce** te permite transformar tu tienda online en un sistema profesional de cotizaciones. Ideal para negocios B2B, productos personalizados, mayoristas o cualquier negocio que requiera presupuestos antes de cerrar ventas.

= 🎯 Características Principales =

* **Gestión de Cotizaciones Flexible:** Habilita productos como "cotizables" de forma individual o global
* **Ocultar Precios:** Esconde precios en páginas de tienda y producto hasta enviar la cotización
* **Botones Personalizables:** Cambia "Añadir al carrito" por "Solicitar Cotización"
* **Sistema de Emails Completo:** 4 tipos de emails automatizados para cada etapa del proceso
* **Sin Pagos Iniciales:** Los clientes solicitan cotizaciones sin procesar pagos
* **Panel de Administración Intuitivo:** Gestiona todas las cotizaciones desde WooCommerce
* **Compatible con Productos Variables:** Soporte completo para variaciones con enlaces inteligentes

= 📧 Sistema de Emails Automatizado (Nuevo en v2.0) =

1. **Email de Solicitud (Admin):** Recibe notificación cuando un cliente solicita cotización
2. **Email de Solicitud (Cliente):** Confirmación automática al cliente de solicitud recibida
3. **Email de Cotización Completa (Nuevo):** Email personalizado con instrucciones de envío cuando la cotización está lista
4. **Email de Envío Final:** Notificación con precios finales y enlace de pago

= ⚙️ Configuración Avanzada =

* Configuración global o por producto individual
* Personalización completa de textos de botones
* Ocultar campos de dirección en checkout (opcional)
* Nombre personalizable para la página del carrito
* Plantillas de email 100% personalizables
* Múltiples idiomas incluidos (ES, FR, NL, RU, EN)

= 🔗 Novedades Versión 2.0 =

* ✨ Nuevo email "Quote Complete" con plantilla profesional
* ✨ Enlaces automáticos a productos variables con atributos en URL
* ✨ Sistema de filtros para personalizar direcciones de envío
* ✨ Botón AJAX para enviar cotizaciones sin recargar página
* ✨ Notas automáticas en pedidos para mejor seguimiento
* 🎨 Plantillas de email mejoradas y más profesionales
* 🐛 Múltiples correcciones de bugs y mejoras de rendimiento

= 💼 Casos de Uso Ideales =

* Tiendas B2B que requieren presupuestos personalizados
* Productos con precios variables según cantidad
* Servicios personalizados que requieren consulta previa
* Mayoristas con precios negociables
* Productos de alto valor que requieren conversación con el cliente
* Negocios de compra-venta de segunda mano
* Tiendas de recompra de dispositivos electrónicos

= 🌍 Traducciones Incluidas =

* Español (es_ES)
* Francés (fr_FR)
* Holandés (nl_NL)
* Ruso (ru_RU)
* Inglés (en_US) - Por defecto

= 🔌 Compatibilidad =

* WordPress 4.5+
* WooCommerce 3.0+
* PHP 7.0+
* Multisite compatible
* WPML y Loco Translate ready
* Compatible con la mayoría de temas de WooCommerce
* YayMail Pro compatible

= 📖 Documentación y Soporte =

* [Documentación Completa](https://github.com/tu-repo/quotes-for-woocommerce/wiki)
* [Preguntas Frecuentes](https://wordpress.org/plugins/quotes-for-woocommerce/faq/)
* [Foro de Soporte](https://wordpress.org/support/plugin/quotes-for-woocommerce/)

== Installation ==

= Instalación Automática =

1. Ve a Plugins > Añadir Nuevo
2. Busca "Quotes for WooCommerce"
3. Haz clic en "Instalar Ahora"
4. Activa el plugin
5. Configura en Quotes > Settings

= Instalación Manual =

1. Descarga el archivo .zip del plugin
2. Ve a Plugins > Añadir Nuevo > Subir Plugin
3. Selecciona el archivo .zip y haz clic en "Instalar Ahora"
4. Activa el plugin
5. Configura en Quotes > Settings

= Configuración Inicial =

1. **Configuración Global:** Ve a Quotes > Settings
   * Marca "Enable Quotes" para habilitar cotizaciones globalmente
   * Personaliza textos de botones
   * Configura opciones de carrito y checkout

2. **Configuración por Producto:** 
   * Edita cualquier producto
   * Ve a la pestaña "Inventory"
   * Marca "Enable Quotes" para ese producto específico
   * Opcionalmente, marca "Display Product Price" si quieres mostrar el precio

3. **Configuración de Emails:**
   * Ve a WooCommerce > Settings > Emails
   * Configura cada tipo de email según tus necesidades
   * Personaliza asuntos y encabezados

== Frequently Asked Questions ==

= ¿Cómo funciona el flujo de cotización? =

1. El cliente navega por la tienda y añade productos cotizables al carrito
2. En el checkout, no se solicita pago, solo información de contacto
3. El admin recibe un email de notificación de nueva solicitud
4. El admin edita el pedido, establece precios y pulsa "Quote Complete"
5. El cliente recibe email con instrucciones
6. Opcionalmente, el admin pulsa "Send Quote" para enviar cotización final con enlace de pago
7. El cliente puede pagar desde el email o su cuenta

= ¿Puedo mezclar productos cotizables y normales en el mismo carrito? =

No. El plugin impide mezclar productos que requieren cotización con productos normales. Si un cliente intenta hacerlo, se vaciará el carrito automáticamente con un mensaje informativo.

= ¿Los precios están completamente ocultos? =

Sí, por defecto los precios se ocultan en:
* Páginas de categoría/tienda
* Página individual de producto
* Carrito
* Checkout
* Página de agradecimiento
* Mi Cuenta > Pedidos

Opcionalmente puedes mostrar precios activando "Display Product Price" por producto o globalmente.

= ¿Puedo personalizar los emails? =

Sí, de dos formas:

1. **Desde el admin:** WooCommerce > Settings > Emails - Personaliza asuntos, encabezados y activa/desactiva emails

2. **Plantillas personalizadas:** Copia las plantillas desde `quotes-for-woocommerce/templates/emails/` a tu tema en `tu-tema/quotes-for-wc/emails/` y edítalas

= ¿Cómo personalizo la dirección de envío en el email? =

Añade esto a tu `functions.php`:

`
add_filter( 'qwc_shipping_address', function( $address ) {
    return 'Tu Dirección Personalizada';
});
`

También disponibles: `qwc_shipping_recipient_name` y `qwc_shipping_phone`

= ¿Funciona con productos variables? =

Sí, perfectamente. Además, en la versión 2.0 los enlaces en emails incluyen automáticamente los atributos de la variación en la URL.

= ¿Puedo usar diferentes textos para el botón "Añadir al carrito"? =

Sí, ve a Quotes > Settings > Shop & Product Page Settings y personaliza el texto del botón.

= ¿El plugin afecta el rendimiento de mi tienda? =

No. El plugin es muy ligero y solo ejecuta código cuando:
* Se muestran productos cotizables
* Se procesa un pedido de cotización
* Se envía un email

No añade consultas pesadas a la base de datos ni ralentiza la navegación.

= ¿Es compatible con mi tema? =

Sí, el plugin está diseñado para ser compatible con cualquier tema que siga los estándares de WooCommerce. Ha sido probado con:
* Storefront
* Astra
* OceanWP
* Flatsome
* Divi
* Y muchos más

= ¿Puedo traducir el plugin a mi idioma? =

Sí, el plugin incluye archivos .pot y es compatible con Loco Translate y WPML. También incluye traducciones a Español, Francés, Holandés y Ruso.

= ¿Cómo desinstalo completamente el plugin? =

Al desactivar y eliminar el plugin, todos los datos se eliminan automáticamente:
* Meta datos de productos
* Meta datos de pedidos
* Opciones globales
* Estados de cotización

Los pedidos existentes NO se eliminan, solo sus metadatos relacionados con cotizaciones.

== Screenshots ==

1. Configuración global del plugin en Quotes > Settings
2. Configuración de cotizaciones a nivel de producto
3. Vista de producto en el frontend con botón "Request Quote"
4. Página de administración de pedidos con botones de cotización
5. Email "Quote Complete" recibido por el cliente
6. Email "Send Quote" con precios y enlace de pago
7. Configuración de emails en WooCommerce > Settings > Emails
8. Carrito con productos cotizables (precios ocultos)

== Changelog ==

= 2.0 (2025-01-XX) =
* ✨ Nuevo: Email "Quote Complete" con plantilla personalizada e instrucciones de envío
* ✨ Nuevo: Enlaces automáticos a productos variables con atributos en URL (query params)
* ✨ Nuevo: Filtros WordPress para personalizar direcciones de envío (recipient, address, phone)
* ✨ Nuevo: Botón AJAX "Quote Complete" en admin que envía email sin recargar
* ✨ Nuevo: Sistema mejorado de notas en pedidos para tracking de emails enviados
* 🎨 Mejorado: Plantillas de email más profesionales y limpias
* 🎨 Mejorado: Separación clara entre "Quote Complete" y "Send Quote"
* 🐛 Corregido: Precios mostrados en admin cuando no deberían
* 🐛 Corregido: Compatibilidad con versiones recientes de WooCommerce
* 📝 Mejorado: Documentación completa con ejemplos de código
* 🔧 Mejorado: Código refactorizado y optimizado

= 1.9 (25.05.2022) =
* Enhancement - Add an order note to the WooCommerce Order once a quote email has been sent
* Enhancement - Added .po file for Spanish. Thank you @contributor
* Tweak - Allow site admin to change the field list displayed/hidden at Checkout when 'Hide Address fields at Checkout' is enabled using a hook
* Tweak - Add hook to change conflict message displayed on the cart page
* Tweak - Added hook to change page title for Checkout page and pay for order page
* Fix - Prices are hidden in the WordPress admin dashboard when quotes are enabled for products

= 1.8 (10.08.2021) =
* Tweak - Added the ability to display item attributes in the initial quote emails
* Tweak - Converted product names to links in quote emails
* Tweak - Included a filter to modify product quote status on the front end

= 1.7.3 (02.05.2021) =
* Enhancement - Made the plugin compatible with YayMail Pro
* Tweak - Added filter to change Payment medium name
* Tweak - Added filter to add new rows to the new quote request email sent to admin
* Fix - Plain Text Email Template file name was incorrect
* Fix - Incorrect text domain listed in the plugin file
* Fix - Incorrect text domain was used for the Subtotal text

= 1.7.2 (03.04.2021) =
* Fix - PHP email templates were being copied to an incorrect location
* Tweak - Added nl_NL, fr_FR, ru_RU translation files

= 1.7.1 (18.01.2021) =
* Fix - Internal Server Error with WooCommerce 4.9.0
* Fix - XML Sitemap showing error
* Tweak - Added hook to allow admin to send emails for statuses other than Pending Payment

= 1.7.0 (20.06.2020) =
* Fix - Price was displayed in html tags in customer email
* Fix - Appearance->menus disappear when the plugin is activated
* Fix - Product Added to Cart message is incomplete when global settings are not saved
* Enhancement - Add setting to change Place Order button text for quotable products
* Fix - Incorrect Add to Cart button text for non quote products without price
* Fix - WordPress crashes when plugin is activated with themes like Jevelin & Avada
* Tweak - Add Settings link on the Plugins page
* Fix - Mini cart displays cart total when quotable products are present

= 1.6.4 (27.01.2020) =
* Fix - Order total prices were displayed with HTML in the Quote emails

= 1.6.3 (05.12.2019) =
* Enhancement - Made the plugin compatible with Loco Translate
* Enhancement - Added billing first name, last name, email & phone merge tags for admin email
* Fix - Prices were displayed on the My Account->Orders page
* Fix - Double headers & footers in the plugin emails
* Fix - Made the plugin WPCS compatible

= 1.6.2 (20.07.2019) =
* Enhancement - Made the plugin translation ready with .pot file
* Enhancement - Added setting to modify Add to Cart button text
* Fix - Updated parameters used in woocommerce_email_before_order_table hook

= 1.6.1 (11.04.2019) =
* Fix - Internal 500 Error when updating to version 1.6
* Fix - Deprecated WooCommerce filter replaced
* Tweak - Modified variables being passed to email templates

= 1.6 (25.03.2019) =
* Added setting to change Cart page name when cart contains only quotable products
* Added setting to disable Billing & Shipping addresses for quote orders
* Fixed conflict error with Stream plugin
* Fixed issue where {blogname} merge tag was not replaced
* Email subject & headings are now customizable from WooCommerce->Settings->Emails
* Fixed error being logged for quote emails

= 1.5 (31.10.2018) =
* Fixed checkout process failure with only variable products
* Added new menu Quote->Settings for global quote settings
* Fixed issue where turning off Quote emails didn't stop them

= 1.4 (19.06.2018) =
* Fixed warning in debug.log when quote email sent
* Added setting to display product prices for quotable products
* Added new email template sent to customer when quote request is raised

= 1.3 (18.12.2017) =
* Fixed Internal Server Error at Checkout with quote products

= 1.2 (28.09.2017) =
* Added email template for admin when quote request is received
* Fixed Add to Cart text not modified on single product page

= 1.1 (03.09.2017) =
* Added code to remove plugin data when deleted
* Added WC Version check support
* Added plugin version data in DB

= 1.0 (29.08.2017) =
* Initial release

== Upgrade Notice ==

= 2.0 =
MAJOR UPDATE: Nueva funcionalidad de email "Quote Complete", enlaces inteligentes a productos variables, sistema de filtros para personalización y múltiples mejoras. Se recomienda hacer backup antes de actualizar.

= 1.9 =
Añade notas automáticas a pedidos, traducciones mejoradas y nuevos hooks de personalización. Actualización recomendada.

= 1.8 =
Mejoras en emails con atributos de producto y enlaces clickeables. Actualización segura.

= 1.7.3 =
Compatibilidad con YayMail Pro y correcciones importantes. Actualización recomendada.

= 1.7.0 =
Añade configuración para texto del botón Place Order y corrige múltiples bugs. Actualización recomendada.

= 1.6 =
Nueva funcionalidad para personalizar nombre del carrito y ocultar direcciones. Actualización segura.

= 1.5 =
Añade configuración global y corrige problemas importantes. Actualización muy recomendada.

== Additional Info ==

= Hooks & Filters =

**Acciones disponibles:**
* `qwc_pending_quote_notification` - Cuando se recibe nueva solicitud
* `qwc_quote_complete_notification` - Cuando se completa cotización (Nuevo en 2.0)
* `qwc_send_quote_notification` - Cuando se envía cotización final
* `qwc_request_sent_notification` - Cuando se confirma solicitud al cliente
* `qwc_new_quote_admin_row` - Para añadir filas en email de admin

**Filtros disponibles:**
* `qwc_shipping_recipient_name` - Personalizar nombre destinatario (Nuevo en 2.0)
* `qwc_shipping_address` - Personalizar dirección de envío (Nuevo en 2.0)
* `qwc_shipping_phone` - Personalizar teléfono (Nuevo en 2.0)
* `qwc_quote_complete_show_order_details` - Mostrar/ocultar detalles en email (Nuevo en 2.0)
* `qwc_payment_method_name` - Cambiar nombre del método de pago
* `qwc_cart_conflict_msg` - Personalizar mensaje de conflicto en carrito
* `qwc_change_checkout_page_title` - Cambiar título de páginas de checkout
* `qwc_edit_allowed_order_statuses_for_sending_quotes` - Estados permitidos para enviar
* `qwc_hide_billing_fields` - Campos de dirección a ocultar
* `qwc_product_quote_enabled` - Controlar estado de cotización por código
* `qwc_quote_complete_email` - Controlar envío de email Quote Complete
* `qwc_send_quote_email` - Controlar envío de email Send Quote
* `qwc_request_sent_email` - Controlar envío de email Request Sent
* `qwc_request_new_quote_email` - Controlar envío de email New Quote (admin)

= Funciones PHP Útiles =

`
// Comprobar si producto tiene cotizaciones habilitadas
product_quote_enabled( $product_id );

// Comprobar si carrito contiene productos cotizables
cart_contains_quotable();

// Comprobar si pedido requiere cotización
order_requires_quote( $order );

// Comprobar si debe mostrarse precio del producto
product_price_display( $product_id );

// Comprobar si debe mostrarse precio en carrito
qwc_cart_display_price();

// Comprobar si debe mostrarse precio en pedido
qwc_order_display_price( $order );
`

= Soporte Técnico =

Para reportar bugs, solicitar características o obtener ayuda:

* [Foro de Soporte WordPress](https://wordpress.org/support/plugin/quotes-for-woocommerce/)
* [GitHub Issues](https://github.com/tu-repo/quotes-for-woocommerce/issues)
* [Documentación](https://github.com/tu-repo/quotes-for-woocommerce/wiki)

= Contribuir =

Este es un proyecto de código abierto. Las contribuciones son bienvenidas:

* [Repositorio GitHub](https://github.com/tu-repo/quotes-for-woocommerce)
* [Guía de Contribución](https://github.com/tu-repo/quotes-for-woocommerce/blob/master/CONTRIBUTING.md)

= Créditos =

* Autor Original: Pinal Shah
* Mantenedor Actual: Mikel Marqués
* Traductores: Comunidad WordPress

= Licencia =

Este plugin está licenciado bajo GPL v3 o posterior.
https://www.gnu.org/licenses/gpl-3.0.html