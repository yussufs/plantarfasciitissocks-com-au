<?php
/**
 * Template Name: Contact Us
 * Template for the "contact-us" page slug.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Flash messages from form submission redirect.
$contact_status = isset( $_GET['contact'] ) ? sanitize_text_field( wp_unslash( $_GET['contact'] ) ) : '';

set_query_var( 'page_subtitle', __( 'Have a question or need help with your order? We\'ll get back to you within 24 hours.', 'brand-theme' ) );
get_template_part( 'template-parts/content/page-header' );
?>

<main class="mx-auto w-full max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

    <?php if ( 'success' === $contact_status ) : ?>
        <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800">
            <?php esc_html_e( 'Thanks for reaching out! We\'ve received your message and will get back to you shortly.', 'brand-theme' ); ?>
        </div>
    <?php elseif ( 'error' === $contact_status ) : ?>
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800">
            <?php esc_html_e( 'Something went wrong sending your message. Please try again or email us directly.', 'brand-theme' ); ?>
        </div>
    <?php endif; ?>

    <!-- Contact Form -->
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="contact-form mt-8 space-y-6">
        <?php wp_nonce_field( 'brand_contact_form', 'brand_contact_nonce' ); ?>
        <input type="hidden" name="action" value="brand_contact_form">

        <!-- Honeypot — hidden from real users -->
        <div class="contact-form-hp" aria-hidden="true">
            <label for="contact-website"><?php esc_html_e( 'Website', 'brand-theme' ); ?></label>
            <input type="text" name="website" id="contact-website" autocomplete="off" tabindex="-1">
        </div>

        <div>
            <label for="contact-name">
                <?php esc_html_e( 'Name', 'brand-theme' ); ?> <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="contact-name"
                name="contact_name"
                required
                class="contact-form-input"
                placeholder="<?php esc_attr_e( 'Your full name', 'brand-theme' ); ?>"
            >
        </div>

        <div>
            <label for="contact-email">
                <?php esc_html_e( 'Email', 'brand-theme' ); ?> <span class="text-red-500">*</span>
            </label>
            <input
                type="email"
                id="contact-email"
                name="contact_email"
                required
                class="contact-form-input"
                placeholder="<?php esc_attr_e( 'you@example.com', 'brand-theme' ); ?>"
            >
        </div>

        <div>
            <label for="contact-message">
                <?php esc_html_e( 'Message', 'brand-theme' ); ?> <span class="text-red-500">*</span>
            </label>
            <textarea
                id="contact-message"
                name="contact_message"
                required
                rows="6"
                class="contact-form-textarea"
                placeholder="<?php esc_attr_e( 'How can we help you?', 'brand-theme' ); ?>"
            ></textarea>
        </div>

        <button type="submit" class="btn-primary w-full rounded-lg px-6 py-4 text-base font-bold">
            <?php esc_html_e( 'Send Message', 'brand-theme' ); ?>
        </button>
    </form>

    <!-- FAQ Section -->
    <section class="mt-16">
        <h2 class="text-2xl font-extrabold text-zinc-900"><?php esc_html_e( 'Frequently Asked Questions', 'brand-theme' ); ?></h2>
        <div class="mt-6 space-y-0 border-t border-zinc-200">

            <details class="product-accordion">
                <summary>
                    <span><?php esc_html_e( 'How do I track my order?', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
                </summary>
                <div class="product-accordion-body">
                    <p><?php esc_html_e( 'Once your order ships, you\'ll receive an email with a tracking number and a link to track your parcel. You can also track your order at any time using the Australia Post tracking page.', 'brand-theme' ); ?></p>
                </div>
            </details>

            <details class="product-accordion">
                <summary>
                    <span><?php esc_html_e( 'What is your return and exchange policy?', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
                </summary>
                <div class="product-accordion-body">
                    <p><?php esc_html_e( 'We offer a 30-day return policy on all unused items in their original packaging. If you\'re not satisfied with your purchase, contact us and we\'ll arrange a return or exchange. Please note that return shipping costs are the responsibility of the customer unless the item is faulty.', 'brand-theme' ); ?></p>
                </div>
            </details>

            <details class="product-accordion">
                <summary>
                    <span><?php esc_html_e( 'How long does shipping take?', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
                </summary>
                <div class="product-accordion-body">
                    <p><?php esc_html_e( 'Standard shipping within Australia typically takes 3-7 business days. Express shipping options are available at checkout for faster delivery. Orders are processed within 1-2 business days.', 'brand-theme' ); ?></p>
                </div>
            </details>

            <details class="product-accordion">
                <summary>
                    <span><?php esc_html_e( 'Can I change or cancel my order?', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
                </summary>
                <div class="product-accordion-body">
                    <p><?php esc_html_e( 'We process orders quickly, so please contact us as soon as possible if you need to make changes. If your order hasn\'t shipped yet, we\'ll do our best to accommodate your request. Once an order has been dispatched, it cannot be modified.', 'brand-theme' ); ?></p>
                </div>
            </details>

            <details class="product-accordion">
                <summary>
                    <span><?php esc_html_e( 'How do I contact support?', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
                </summary>
                <div class="product-accordion-body">
                    <p><?php esc_html_e( 'You can reach us using the contact form above, or email us directly at the address listed on this page. We aim to respond to all enquiries within 24 hours during business days.', 'brand-theme' ); ?></p>
                </div>
            </details>

        </div>
    </section>

</main>

<?php
get_footer();
