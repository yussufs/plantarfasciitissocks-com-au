<?php
/**
 * Template Name: Contact
 * Template for the "contact" page slug.
 *
 * Renders the contact form (name, email, message + honeypot). Submission is
 * handled by brand_theme_handle_contact_form() in functions.php, which emails
 * the site admin with the sender's address as Reply-To.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Flash message from the form submission redirect.
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

        <!-- Honeypot — hidden from real users, bots fill it in -->
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

</main>

<?php
get_footer();
